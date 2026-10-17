#!/usr/bin/env bash
#
# SAMS – Server-Einrichtung für Ubuntu/Debian
#
# Richtet alles ein, was für den Betrieb mit Auto-Update nötig ist:
#   Pakete, Systembenutzer, Deploy-Key, Code, .env, Datenbank, Webserver,
#   HTTPS, Admin-Konto, Update-Timer (alle 2 Minuten) und tägliche Backups.
#
# Aufruf (als root):
#   sudo bash install.sh
#
# Das Skript kann gefahrlos erneut ausgeführt werden (z. B. um Einstellungen
# zu ändern oder einen abgebrochenen Lauf fortzusetzen).
#
# Optionale Umgebungsvariablen:
#   SAMS_REPO   Git-Adresse   (Standard: git@github.com:Wolfsrudel-Gaming/SAMS-Documents.git)
#   SAMS_DIR    Zielordner    (Standard: /var/www/sams)
#   SAMS_USER   Systembenutzer (Standard: sams)
# Unbeaufsichtigt (ohne Rückfragen):
#   SAMS_MODE_CHOICE=1|2  SAMS_DOMAIN=…  SAMS_BRANCH=…  SAMS_HTTPS=0|1  SAMS_LE_MAIL=…
#   SAMS_ADMIN_USER=…  SAMS_ADMIN_NAME=…  SAMS_ADMIN_PASS=…
#
set -euo pipefail

REPO_URL="${SAMS_REPO:-git@github.com:Wolfsrudel-Gaming/SAMS-Documents.git}"
APP_DIR="${SAMS_DIR:-/var/www/sams}"
APP_USER="${SAMS_USER:-sams}"
APP_HOME="/home/$APP_USER"

# ---------------------------------------------------------------- Hilfen ---
if [[ -t 1 ]]; then B=$'\e[1m'; G=$'\e[32m'; Y=$'\e[33m'; R=$'\e[31m'; C=$'\e[36m'; N=$'\e[0m'; else B= G= Y= R= C= N=; fi
step() { echo; echo "${B}${C}==> $*${N}"; }
ok()   { echo "  ${G}✔${N} $*"; }
warn() { echo "  ${Y}!${N} $*"; }
die()  { echo "${R}✖ $*${N}" >&2; exit 1; }
ask() { # ask VAR "Frage" "Standard"  – unbeaufsichtigt: Umgebungsvariable SAMS_<VAR> setzen
    local __var="$1" __q="$2" __def="${3:-}" __ans __env="SAMS_$1"
    if [[ -n "${!__env:-}" ]]; then printf -v "$__var" '%s' "${!__env}"; echo "  $__q: ${!__env}"; return; fi
    read -r -p "  $__q${__def:+ [$__def]}: " __ans </dev/tty || true
    printf -v "$__var" '%s' "${__ans:-$__def}"
}
ask_yn() { # ask_yn "Frage" j|n [ENV]  → Rückgabe 0 = ja
    local __ans __def="${2:-j}" __env="${3:-}"
    if [[ -n "$__env" && -n "${!__env:-}" ]]; then [[ "${!__env}" == 1 || "${!__env,,}" == j* ]]; return; fi
    read -r -p "  $1 [$( [[ $__def == j ]] && echo J/n || echo j/N )]: " __ans </dev/tty || true
    __ans="${__ans:-$__def}"
    [[ "${__ans,,}" == j* || "${__ans,,}" == y* ]]
}
as_app() { sudo -u "$APP_USER" -H "$@"; }
env_get() { grep -E "^$1=" "$APP_DIR/.env" 2>/dev/null | tail -n1 | cut -d= -f2- || true; }
env_set() { # env_set KEY VALUE
    local key="$1" val="$2" esc
    esc="$(printf '%s' "$val" | sed -e 's/[\/&|]/\\&/g')"
    if grep -qE "^$key=" "$APP_DIR/.env"; then
        sed -i "s|^$key=.*|$key=$esc|" "$APP_DIR/.env"
    else
        echo "$key=$val" >> "$APP_DIR/.env"
    fi
}
rand() { openssl rand -hex "${1:-24}"; }
has_systemd() { [[ -d /run/systemd/system ]] && [[ "${SAMS_SKIP_SYSTEMD:-0}" != 1 ]]; }
svc_up() { # Dienst aktivieren und starten bzw. neu laden
    if has_systemd; then
        systemctl enable "$1" >/dev/null 2>&1 || true
        systemctl reload-or-restart "$1" >/dev/null 2>&1 || systemctl start "$1"
    elif service "$1" status >/dev/null 2>&1; then
        service "$1" reload >/dev/null 2>&1 || service "$1" restart >/dev/null 2>&1
    else
        service "$1" start >/dev/null 2>&1
    fi
}

[[ $EUID -eq 0 ]] || die "Bitte als root ausführen: sudo bash $0"
command -v apt-get >/dev/null || die "Dieses Skript unterstützt Ubuntu/Debian (apt)."

echo "${B}SAMS Dokumenten- & Aktensystem – Server-Einrichtung${N}"
echo "Ziel: $APP_DIR · Benutzer: $APP_USER · Repository: $REPO_URL"

# ------------------------------------------------------------- Fragen ------
step "1/10 Einstellungen"
CUR_BRANCH="$(env_get UPDATE_BRANCH)"
CUR_URL="$(env_get APP_URL)"
CUR_MODE="native"; [[ "$(env_get UPDATE_PHP_CMD)" == *docker* ]] && CUR_MODE="docker"
echo "  Betriebsart:  1) Docker (empfohlen, alles in Containern)   2) Klassisch (nginx + PHP-FPM + MariaDB direkt)"
ask MODE_CHOICE "Auswahl 1 oder 2" "$( [[ $CUR_MODE == native && -f $APP_DIR/.env ]] && echo 2 || echo 1 )"
MODE="docker"; [[ "$MODE_CHOICE" == 2 ]] && MODE="native"
DEF_DOMAIN="${CUR_URL#*://}"; DEF_DOMAIN="${DEF_DOMAIN%%/*}"
ask DOMAIN "Domain, unter der das System erreichbar sein soll (z. B. sams.deinserver.de)" "${DEF_DOMAIN:-}"
[[ -n "$DOMAIN" ]] || die "Ohne Domain geht es nicht (für HTTPS, QR-Codes und Webhook)."
ask BRANCH "Branch, dem der Server automatisch folgen soll" "${CUR_BRANCH:-main}"
HTTPS=0
if [[ ! "$DOMAIN" =~ ^[0-9.]+$ && "$DOMAIN" != localhost ]] && ask_yn "HTTPS-Zertifikat über Let's Encrypt einrichten? (Domain muss bereits auf diesen Server zeigen)" j SAMS_HTTPS; then
    HTTPS=1
    ask LE_MAIL "E-Mail für Let's Encrypt (Ablauf-Warnungen)" ""
fi
SCHEME="http"; [[ $HTTPS == 1 ]] && SCHEME="https"

# ------------------------------------------------------------- Pakete ------
step "2/10 Pakete installieren"
export DEBIAN_FRONTEND=noninteractive
PKGS=(git curl openssl ca-certificates sudo nginx cron)
if [[ $MODE == native ]]; then
    PKGS+=(mariadb-server php-fpm php-cli php-mysql php-gd php-mbstring php-sqlite3)
fi
[[ $HTTPS == 1 ]] && PKGS+=(certbot python3-certbot-nginx)
MISSING=()
for p in "${PKGS[@]}"; do dpkg -s "$p" >/dev/null 2>&1 || MISSING+=("$p"); done
if [[ ${#MISSING[@]} -gt 0 ]]; then
    echo "  Installiere: ${MISSING[*]}"
    apt-get update -qq && apt-get install -y -qq "${MISSING[@]}" >/dev/null
fi
ok "Grundpakete vorhanden"
if [[ $MODE == docker ]]; then
    if ! command -v docker >/dev/null || ! docker compose version >/dev/null 2>&1; then
        echo "  Installiere Docker …"
        apt-get install -y -qq docker.io docker-compose-v2 >/dev/null 2>&1 \
            || apt-get install -y -qq docker.io docker-compose-plugin >/dev/null 2>&1 \
            || die "Docker konnte nicht installiert werden. Anleitung: https://docs.docker.com/engine/install/"
        svc_up docker || true
    fi
    docker compose version >/dev/null 2>&1 || die "'docker compose' fehlt."
    ok "Docker vorhanden ($(docker --version | cut -d, -f1))"
fi

# ---------------------------------------------------------- Benutzer -------
step "3/10 Systembenutzer '$APP_USER'"
if ! id "$APP_USER" >/dev/null 2>&1; then
    useradd --system --create-home --home-dir "$APP_HOME" --shell /bin/bash "$APP_USER"
    ok "Benutzer angelegt"
else
    ok "Benutzer existiert"
fi
usermod -aG www-data "$APP_USER"
[[ $MODE == docker ]] && usermod -aG docker "$APP_USER"

# --------------------------------------------------------- Deploy-Key ------
step "4/10 Zugriff auf GitHub (Deploy-Key)"
if [[ "$REPO_URL" == git@* || "$REPO_URL" == ssh://* ]]; then
    as_app mkdir -p "$APP_HOME/.ssh"
    chmod 700 "$APP_HOME/.ssh"
    [[ -f "$APP_HOME/.ssh/id_ed25519" ]] || as_app ssh-keygen -q -t ed25519 -N "" -C "sams-deploy@$(hostname)" -f "$APP_HOME/.ssh/id_ed25519"
    as_app touch "$APP_HOME/.ssh/known_hosts"
    grep -q "github.com" "$APP_HOME/.ssh/known_hosts" || ssh-keyscan -t ed25519,rsa github.com 2>/dev/null | as_app tee -a "$APP_HOME/.ssh/known_hosts" >/dev/null
    until as_app git ls-remote --heads "$REPO_URL" >/dev/null 2>&1; do
        echo
        echo "  Der Server hat noch keinen Lesezugriff auf das Repository."
        echo "  Trage diesen Schlüssel in GitHub ein:"
        echo "  ${B}Repository → Settings → Deploy keys → Add deploy key${N}"
        echo "  Titel: SAMS-Server $(hostname) · ${B}\"Allow write access\" NICHT anhaken${N}"
        echo
        echo "  ${C}$(cat "$APP_HOME/.ssh/id_ed25519.pub")${N}"
        echo
        read -r -p "  Enter drücken, sobald der Schlüssel eingetragen ist (Strg+C zum Abbrechen) … " _ </dev/tty
    done
    ok "GitHub-Zugriff funktioniert"
else
    as_app git ls-remote --heads "$REPO_URL" >/dev/null 2>&1 || die "Repository nicht erreichbar: $REPO_URL"
    ok "Repository erreichbar"
fi
if ! as_app git ls-remote --exit-code --heads "$REPO_URL" "$BRANCH" >/dev/null 2>&1; then
    echo "  Vorhandene Branches:"; as_app git ls-remote --heads "$REPO_URL" | sed 's#.*refs/heads/#    - #'
    die "Branch '$BRANCH' existiert nicht. Bitte erneut ausführen und einen vorhandenen Branch angeben."
fi

# ------------------------------------------------------------- Code --------
step "5/10 Code holen"
mkdir -p "$(dirname "$APP_DIR")"
if [[ -d "$APP_DIR/.git" ]]; then
    as_app git -C "$APP_DIR" remote set-url origin "$REPO_URL"
    as_app git -C "$APP_DIR" fetch -q origin "$BRANCH"
    as_app git -C "$APP_DIR" checkout -q -B "$BRANCH" "origin/$BRANCH"
    ok "Vorhandene Installation auf origin/$BRANCH gebracht"
else
    if [[ -e "$APP_DIR" && -n "$(ls -A "$APP_DIR" 2>/dev/null)" ]]; then
        die "$APP_DIR existiert bereits und ist kein Git-Checkout. Bitte leeren oder SAMS_DIR setzen."
    fi
    mkdir -p "$APP_DIR"; chown "$APP_USER:www-data" "$APP_DIR"
    as_app git clone -q -b "$BRANCH" "$REPO_URL" "$APP_DIR"
    ok "Geklont nach $APP_DIR"
fi
cd "$APP_DIR"
chmod +x bin/sams bin/update.sh deploy/*.sh 2>/dev/null || true
ok "Stand: $(as_app git log -1 --format='%h %s')"

# ------------------------------------------------------------- .env --------
step "6/10 Konfiguration (.env)"
if [[ ! -f .env ]]; then
    cp .env.example .env
    env_set DB_PASSWORD "$(rand 18)"
    env_set DB_ROOT_PASSWORD "$(rand 18)"
    env_set API_KEY "$(rand 24)"
    env_set UPDATE_WEBHOOK_SECRET "$(rand 20)"
    ok ".env erzeugt (Passwörter und Schlüssel zufällig generiert)"
else
    ok ".env vorhanden – Zugangsdaten bleiben erhalten"
fi
env_set APP_URL "$SCHEME://$DOMAIN"
env_set DB_DRIVER mysql
env_set UPDATE_BRANCH "$BRANCH"
env_set UPDATE_REMOTE origin
[[ -n "$(env_get UPDATE_WEBHOOK_SECRET)" ]] || env_set UPDATE_WEBHOOK_SECRET "$(rand 20)"
if [[ $MODE == docker ]]; then
    env_set DB_HOST db
    env_set UPDATE_PHP_CMD "docker compose exec -T app php"
    grep -qE '^WEB_PORT=' .env || echo "WEB_PORT=8080" >> .env
else
    env_set DB_HOST 127.0.0.1
    env_set UPDATE_PHP_CMD php
fi
chown "$APP_USER:www-data" .env; chmod 640 .env
mkdir -p storage/logs storage/backups storage/update
chown -R "$APP_USER:www-data" storage
chmod -R ug+rwX,o-rwx storage
find storage -type d -exec chmod g+s {} +
ok "Rechte für storage/ gesetzt"
PHP_CMD="$(env_get UPDATE_PHP_CMD)"
DB_NAME="$(env_get DB_DATABASE)"; DB_USER="$(env_get DB_USERNAME)"; DB_PASS="$(env_get DB_PASSWORD)"

# ------------------------------------------------- Datenbank & App ---------
step "7/10 Datenbank und Anwendung starten"
if [[ $MODE == native ]]; then
    svc_up mariadb || die "MariaDB konnte nicht gestartet werden."
    mysql -uroot <<SQL
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
ALTER USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
SQL
    ok "MariaDB-Datenbank '$DB_NAME' bereit"
    PHP_VER="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')"
    svc_up "php$PHP_VER-fpm" || die "PHP-FPM konnte nicht gestartet werden."
    FPM_SOCK="/run/php/php$PHP_VER-fpm.sock"
    ok "PHP $PHP_VER (FPM: $FPM_SOCK)"
else
    as_app docker compose up -d --build 2>&1 | tail -n 3 | sed 's/^/    /'
    echo -n "  Warte auf Datenbank "
    for _ in $(seq 1 60); do
        as_app $PHP_CMD -r 'exit(0);' >/dev/null 2>&1 && as_app $PHP_CMD bin/sams status 2>/dev/null | grep -q "verbunden" && break
        echo -n "."; sleep 2
    done; echo
    ok "Container laufen"
fi
as_app $PHP_CMD bin/sams migrate | sed 's/^/    /'
USERS="$(as_app $PHP_CMD bin/sams status 2>/dev/null | sed -n 's/^Benutzer: //p')"
if [[ "${USERS:-0}" == 0 ]]; then
    echo "  Lege jetzt dein Hauptadministrator-Konto an:"
    ask ADMIN_USER "Benutzername" "admin"
    if [[ -n "${SAMS_ADMIN_NAME:-}" && -n "${SAMS_ADMIN_PASS:-}" ]]; then
        printf '%s\n%s\n' "$SAMS_ADMIN_NAME" "$SAMS_ADMIN_PASS" | as_app $PHP_CMD bin/sams create-admin "$ADMIN_USER"
    elif [[ $MODE == docker ]]; then
        (cd "$APP_DIR" && sudo -u "$APP_USER" -H docker compose exec app php bin/sams create-admin "$ADMIN_USER") </dev/tty
    else
        sudo -u "$APP_USER" -H php bin/sams create-admin "$ADMIN_USER" </dev/tty
    fi
else
    ok "$USERS Benutzerkonto/-konten vorhanden"
fi

# ------------------------------------------------------------- nginx -------
step "8/10 Webserver (nginx)"
SITE="/etc/nginx/sites-available/$APP_USER"
if [[ $MODE == native ]]; then
    sed -e "s#server_name sams.example.com;#server_name $DOMAIN;#" \
        -e "s#root /var/www/sams/public;#root $APP_DIR/public;#" \
        -e "s#unix:/run/php/php8.3-fpm.sock#unix:$FPM_SOCK#" deploy/nginx.conf > "$SITE"
else
    PORT="$(env_get WEB_PORT)"
    sed -e "s#server_name sams.example.com;#server_name $DOMAIN;#" -e "s#127.0.0.1:8080#127.0.0.1:${PORT:-8080}#" deploy/nginx-proxy.conf > "$SITE"
fi
ln -sf "$SITE" "/etc/nginx/sites-enabled/$APP_USER"
if [[ -L /etc/nginx/sites-enabled/default ]] && grep -q "Welcome to nginx\|default_server" /etc/nginx/sites-enabled/default 2>/dev/null; then
    rm -f /etc/nginx/sites-enabled/default
    warn "nginx-Standardseite deaktiviert"
fi
nginx -t >/dev/null 2>&1 || { nginx -t; die "nginx-Konfiguration fehlerhaft."; }
svc_up nginx || die "nginx konnte nicht gestartet werden."
ok "nginx eingerichtet für $DOMAIN"
if [[ $HTTPS == 1 ]]; then
    if certbot --nginx -d "$DOMAIN" --non-interactive --agree-tos --redirect ${LE_MAIL:+-m "$LE_MAIL"} ${LE_MAIL:---register-unsafely-without-email} >/dev/null 2>&1; then
        ok "HTTPS-Zertifikat eingerichtet (verlängert sich automatisch)"
    else
        warn "Zertifikat fehlgeschlagen – zeigt die Domain schon auf diesen Server? Später erneut: certbot --nginx -d $DOMAIN"
    fi
fi

# ----------------------------------------------------------- Timer ---------
step "9/10 Auto-Update und Backups"
install_unit() { # Vorlage anpassen: Pfad, Benutzer, Docker
    sed -e "s#/var/www/sams#$APP_DIR#g" -e "s#^User=sams#User=$APP_USER#" "deploy/systemd/$1" > "/etc/systemd/system/$1"
}
if has_systemd; then
    for u in sams-update.service sams-update.timer sams-update.path sams-backup.service sams-backup.timer; do install_unit "$u"; done
    if [[ $MODE == docker ]]; then
        sed -i "s#^Group=www-data#Group=www-data\nSupplementaryGroups=docker#" /etc/systemd/system/sams-update.service /etc/systemd/system/sams-backup.service
        sed -i -e "s#^ExecStart=/usr/bin/php .*#ExecStart=/usr/bin/docker compose -f $APP_DIR/docker-compose.yml exec -T app php bin/sams backup daily#" \
               -e "/^# Ohne Docker:/d" -e "/^# Mit Docker stattdessen:/d" -e "/^# ExecStart=.*docker/d" /etc/systemd/system/sams-backup.service
    else
        sed -i -e "s#^ExecStart=/usr/bin/php #ExecStart=$(command -v php) #" /etc/systemd/system/sams-backup.service
    fi
    systemctl daemon-reload
    systemctl enable --now sams-update.timer sams-update.path sams-backup.timer >/dev/null 2>&1
    ok "systemd: Update-Prüfung alle 2 Minuten, Sofort-Update per Webhook, Backup täglich 04:30"
else
    CRON="* * * * * $APP_DIR/bin/update.sh >/dev/null 2>&1
30 4 * * * cd $APP_DIR && $PHP_CMD bin/sams backup daily >/dev/null 2>&1"
    echo "$CRON" | crontab -u "$APP_USER" -
    svc_up cron || warn "Cron-Dienst läuft nicht – bitte prüfen."
    warn "Kein systemd gefunden – Cron eingerichtet (Update-Prüfung jede Minute, Backup 04:30)"
fi

# Bequeme Befehle: "sams status", "sams-update" (bei abweichendem Benutzer nach diesem benannt)
CMD="$APP_USER"
cat > "/usr/local/bin/$CMD" <<EOF
#!/bin/sh
# SAMS-Kommandozeile (läuft als Benutzer $APP_USER)
cd "$APP_DIR" && exec sudo -u "$APP_USER" -H $PHP_CMD bin/sams "\$@"
EOF
cat > "/usr/local/bin/$CMD-update" <<EOF
#!/bin/sh
# SAMS-Update sofort ausführen
cd "$APP_DIR" && exec sudo -u "$APP_USER" -H bin/update.sh "\$@"
EOF
chmod 755 "/usr/local/bin/$CMD" "/usr/local/bin/$CMD-update"
ok "Befehle installiert: ${B}$CMD status${N}, ${B}$CMD backup${N}, ${B}$CMD-update${N}"

# ------------------------------------------------------------ Prüfung ------
step "10/10 Prüfung"
as_app bin/update.sh && ok "Update-Skript läuft (Stand ist aktuell)"
HEALTH=""
for _ in 1 2 3 4 5 6; do   # nginx braucht nach dem Neuladen einen Moment
    if [[ $HTTPS == 1 ]]; then
        HEALTH="$(curl -fsSk -m 10 --resolve "$DOMAIN:443:127.0.0.1" "https://$DOMAIN/health.php" 2>/dev/null || true)"
    fi
    [[ -n "$HEALTH" ]] || HEALTH="$(curl -fsS -m 10 -H "Host: $DOMAIN" http://127.0.0.1/health.php 2>/dev/null || true)"
    [[ "$HEALTH" == *"$(as_app git log -1 --format=%h)"* ]] && break
    sleep 2
done
[[ "$HEALTH" == *'"status":"ok"'* ]] && ok "Webseite antwortet: $HEALTH" || warn "health.php antwortet nicht wie erwartet: ${HEALTH:-keine Antwort}"

echo
echo "${B}${G}Fertig!${N}"
echo
echo "  Adresse:        ${B}$SCHEME://$DOMAIN${N}"
echo "  Auto-Update:    folgt Branch ${B}$BRANCH${N} – neue Commits sind nach spätestens ~2 Minuten live"
echo "  Status:         $CMD status   ·   Admin-Bereich → System & Updates"
echo "  Update-Log:     $APP_DIR/storage/logs/update.log"
echo
echo "  ${B}FiveM-Resource (sams_tablet) – in die server.cfg eintragen:${N}"
echo "    set sams_url \"$SCHEME://$DOMAIN\""
echo "    set sams_api_key \"$(env_get API_KEY)\""
echo "    ensure sams_tablet"
echo
echo "  ${B}Optional – Sofort-Update per GitHub-Webhook:${N}"
echo "  GitHub → Repository → Settings → Webhooks → Add webhook"
echo "    Payload URL:   $SCHEME://$DOMAIN/hooks/github.php"
echo "    Content type:  application/json"
echo "    Secret:        $(env_get UPDATE_WEBHOOK_SECRET)"
echo "    Events:        Just the push event"
echo
