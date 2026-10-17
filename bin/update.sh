#!/usr/bin/env bash
#
# SAMS Auto-Update
#
# Prüft, ob es auf GitHub einen neuen Stand gibt, und spielt ihn ein:
#   1. git fetch – nichts zu tun, wenn der Stand aktuell ist
#   2. Datenbank-Backup
#   3. Wartungsmodus an
#   4. Code auf den neuen Stand bringen (lokale Änderungen werden als Patch gesichert)
#   5. Datenbank-Migrationen
#   6. Wartungsmodus aus
# Schlägt Schritt 5 fehl, werden Code und Datenbank automatisch zurückgesetzt.
#
# Ein fehlgeschlagener Commit wird gemerkt und erst wieder versucht, wenn ein neuer
# Commit kommt oder die Prüfung manuell angefordert wird (Button im Admin, --force).
#
# Aufruf regelmäßig per systemd-Timer oder Cron (siehe deploy/), z. B. alle 2 Minuten.
#   bin/update.sh           normal (nur wenn es Neues gibt)
#   bin/update.sh --force   auch ohne neuen Commit bzw. einen fehlgeschlagenen erneut versuchen
#
set -uo pipefail
umask 0002

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT" || exit 1

STORAGE="$ROOT/storage"
LOG="$STORAGE/logs/update.log"
mkdir -p "$STORAGE/logs" "$STORAGE/update"

# Einstellungen aus .env lesen (echte Umgebungsvariablen haben Vorrang)
env_get() {
    local key="$1" default="${2:-}" value=""
    if [[ -n "${!key:-}" ]]; then
        echo "${!key}"; return
    fi
    if [[ -f "$ROOT/.env" ]]; then
        value="$(grep -E "^${key}=" "$ROOT/.env" | tail -n1 | cut -d= -f2- | sed -e 's/^["'\'']//' -e 's/["'\'']$//')"
    fi
    echo "${value:-$default}"
}

BRANCH="$(env_get UPDATE_BRANCH main)"
REMOTE="$(env_get UPDATE_REMOTE origin)"
PHP_CMD="$(env_get UPDATE_PHP_CMD php)"
RELOAD_CMD="$(env_get UPDATE_RELOAD_CMD)"
DISCORD="$(env_get UPDATE_DISCORD_WEBHOOK)"
FORCE=0
[[ "${1:-}" == "--force" ]] && FORCE=1

export TZ="$(env_get APP_TIMEZONE Europe/Berlin)"

log() { echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*" | tee -a "$LOG"; }

notify() {
    [[ -z "$DISCORD" ]] && return 0
    local msg="${1//\"/\\\"}"
    curl -fsS -m 10 -H 'Content-Type: application/json' \
        -d "{\"username\":\"SAMS Update\",\"content\":\"$msg\"}" "$DISCORD" >/dev/null 2>&1 || true
}

write_status() {
    # status, from, to, message
    printf '{"status":"%s","from":"%s","to":"%s","message":"%s","finished_at":"%s"}\n' \
        "$1" "$2" "$3" "${4//\"/\'}" "$(date '+%Y-%m-%d %H:%M:%S')" > "$STORAGE/update/last.json"
}

php_run() { $PHP_CMD bin/sams "$@"; }

# Docker-Betrieb: Container müssen laufen, bevor PHP-Befehle darin ausgeführt werden
DOCKER=0
[[ "$PHP_CMD" == *"docker compose"* || "$PHP_CMD" == *"docker-compose"* ]] && DOCKER=1
ensure_docker() {
    [[ $DOCKER -eq 1 ]] || return 0
    docker compose up -d "$@" >>"$LOG" 2>&1 || { log "FEHLER: docker compose up fehlgeschlagen."; return 1; }
    for _ in $(seq 1 30); do
        $PHP_CMD -r 'exit(0);' >/dev/null 2>&1 && return 0
        sleep 2
    done
    log "FEHLER: App-Container antwortet nicht."
    return 1
}

# Nur eine Instanz gleichzeitig
exec 9>"$STORAGE/update/update.lock"
flock -n 9 || exit 0

touch "$STORAGE/update/last-check"

REQUESTED=0
if [[ -f "$STORAGE/update/request" ]]; then
    REQUESTED=1
    rm -f "$STORAGE/update/request"
fi

if ! git fetch --quiet "$REMOTE" "$BRANCH" 2>>"$LOG"; then
    log "FEHLER: git fetch $REMOTE $BRANCH fehlgeschlagen (Netzwerk oder Zugriffsrechte/Deploy-Key prüfen)."
    exit 1
fi

OLD="$(git rev-parse HEAD)"
NEW="$(git rev-parse "$REMOTE/$BRANCH")"

if [[ "$OLD" == "$NEW" && $FORCE -eq 0 ]]; then
    [[ $REQUESTED -eq 1 ]] && log "Manuelle Prüfung: bereits aktuell (${OLD:0:7})."
    exit 0
fi

# Einen bereits fehlgeschlagenen Commit nicht alle paar Minuten erneut versuchen
FAILED_FILE="$STORAGE/update/failed-commit"
if [[ -f "$FAILED_FILE" && "$(cat "$FAILED_FILE")" == "$NEW" && $FORCE -eq 0 && $REQUESTED -eq 0 ]]; then
    exit 0
fi

log "Update ${OLD:0:7} → ${NEW:0:7} (Branch $BRANCH) gestartet."

ensure_docker || { write_status "failed" "$OLD" "$NEW" "Docker-Container nicht bereit"; exit 1; }

# 1. Backup
BACKUP="$(php_run backup pre-update 2>>"$LOG" | sed -n 's/^Backup erstellt: //p')"
if [[ -z "$BACKUP" ]]; then
    log "FEHLER: Backup fehlgeschlagen – Update abgebrochen."
    write_status "failed" "$OLD" "$NEW" "Backup fehlgeschlagen"
    notify "⚠️ SAMS-Update abgebrochen: Backup fehlgeschlagen."
    exit 1
fi
log "Backup: $(basename "$BACKUP")"

# 2. Wartungsmodus
echo "update ${NEW:0:7}" > "$STORAGE/maintenance"

# 3. Code aktualisieren – lokale Änderungen an versionierten Dateien sichern statt verlieren
if [[ -n "$(git status --porcelain --untracked-files=no)" ]]; then
    PATCH="$STORAGE/update/local-changes-$(date '+%Y%m%d-%H%M%S').patch"
    git diff > "$PATCH"
    log "WARNUNG: Lokale Änderungen am Code gefunden und gesichert: $(basename "$PATCH")"
fi
if ! git reset --hard --quiet "$NEW" 2>>"$LOG"; then
    log "FEHLER: git reset fehlgeschlagen."
    rm -f "$STORAGE/maintenance"
    write_status "failed" "$OLD" "$NEW" "git reset fehlgeschlagen"
    notify "⚠️ SAMS-Update fehlgeschlagen (git reset)."
    exit 1
fi
chmod +x bin/sams bin/update.sh deploy/*.sh 2>/dev/null || true

# Docker-Image neu bauen, wenn sich das Docker-Setup geändert hat
if [[ $DOCKER -eq 1 ]] && git diff --name-only "$OLD" "$NEW" | grep -qE '^(Dockerfile|docker-compose\.yml|docker/)'; then
    log "Docker-Setup geändert – baue Container neu."
    ensure_docker --build || log "WARNUNG: Neubau fehlgeschlagen, verwende bestehende Container."
fi

# 4. Migrationen
if ! php_run migrate >>"$LOG" 2>&1; then
    log "FEHLER: Migration fehlgeschlagen – setze Code und Datenbank auf ${OLD:0:7} zurück."
    git reset --hard --quiet "$OLD"
    php_run restore "$BACKUP" --force >>"$LOG" 2>&1 || log "FEHLER: Wiederherstellung des Backups fehlgeschlagen! Manuell prüfen: $BACKUP"
    rm -f "$STORAGE/maintenance"
    echo "$NEW" > "$FAILED_FILE"
    write_status "rolled_back" "$OLD" "$NEW" "Migration fehlgeschlagen, zurückgesetzt"
    notify "⚠️ SAMS-Update auf ${NEW:0:7} fehlgeschlagen und automatisch zurückgesetzt. Details: storage/logs/update.log"
    exit 1
fi

# 5. Optional: PHP-FPM neu laden (OPcache)
if [[ -n "$RELOAD_CMD" ]]; then
    bash -c "$RELOAD_CMD" >>"$LOG" 2>&1 || log "WARNUNG: Reload-Befehl fehlgeschlagen: $RELOAD_CMD"
fi

rm -f "$STORAGE/maintenance" "$FAILED_FILE"
SUBJECT="$(git log -1 --pretty=%s "$NEW")"
write_status "ok" "$OLD" "$NEW" "$SUBJECT"
log "Update erfolgreich: ${NEW:0:7} – $SUBJECT"
notify "✅ SAMS aktualisiert auf \`${NEW:0:7}\`: $SUBJECT"
exit 0
