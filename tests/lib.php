<?php
/**
 * Kleine Test-Werkzeuge: HTTP-Client mit Cookies (eine Sitzung pro Client),
 * Prüfungen und Zugriff auf die Testdatenbank.
 */
declare(strict_types=1);

final class Client
{
    public string $body = '';
    public int $status = 0;
    public string $url = '';
    private string $jar;

    public function __construct(private string $base)
    {
        $this->jar = tempnam(sys_get_temp_dir(), 'samsjar');
    }

    public function get(string $path): self
    {
        return $this->request('GET', $path);
    }

    /** POST; ohne _csrf wird das Token der zuletzt geladenen Seite verwendet. */
    public function post(string $path, array $data = []): self
    {
        if (!isset($data['_csrf']) && preg_match('/name="_csrf" value="([^"]+)"/', $this->body, $m)) {
            $data['_csrf'] = $m[1];
        }
        return $this->request('POST', $path, $data);
    }

    /** Formular mit Datei-Upload (multipart). $files: ['feld' => '/pfad/zur/datei'] */
    public function upload(string $path, array $data, array $files): self
    {
        if (!isset($data['_csrf']) && preg_match('/name="_csrf" value="([^"]+)"/', $this->body, $m)) {
            $data['_csrf'] = $m[1];
        }
        foreach ($files as $field => $file) {
            $data[$field] = new CURLFile($file, mime_content_type($file) ?: 'application/octet-stream', basename($file));
        }
        $ch = curl_init($this->base . $path);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_COOKIEJAR => $this->jar,
            CURLOPT_COOKIEFILE => $this->jar, CURLOPT_POST => true, CURLOPT_POSTFIELDS => $data]);
        $this->body = (string) curl_exec($ch);
        $this->status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $this->url = (string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        return $this;
    }

    public function login(string $user, string $password = 'TestPasswort123'): self
    {
        $this->get('/index.php?p=login');
        return $this->post('/index.php?p=login', ['username' => $user, 'password' => $password]);
    }

    /** Sitzungs-ID aus dem Cookie (für Tests, die die Sitzung altern lassen). */
    public function sessionId(): string
    {
        return preg_match('/SAMSSESSID\s+(\S+)/', (string) @file_get_contents($this->jar), $m) ? $m[1] : '';
    }

    public function has(string $needle): bool
    {
        return str_contains($this->body, $needle);
    }

    public function match(string $regex): ?string
    {
        return preg_match($regex, $this->body, $m) ? ($m[1] ?? $m[0]) : null;
    }

    private function request(string $method, string $path, array $data = []): self
    {
        $ch = curl_init(str_starts_with($path, 'http') ? $path : $this->base . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_COOKIEJAR => $this->jar, CURLOPT_COOKIEFILE => $this->jar,
            CURLOPT_TIMEOUT => 30, CURLOPT_HTTPHEADER => ['Accept-Language: de'],
        ]);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, self::encode($data));
        }
        $this->body = (string) curl_exec($ch);
        $this->status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $this->url = (string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        return $this;
    }

    /** Formularkodierung mit Unterstützung für Listen: ['a[]' => ['1', '2']]. */
    private static function encode(array $data): string
    {
        $parts = [];
        foreach ($data as $k => $v) {
            foreach ((array) $v as $item) {
                $parts[] = rawurlencode((string) $k) . '=' . rawurlencode((string) $item);
            }
        }
        return implode('&', $parts);
    }
}

/** JSON-API des Systems (wie die FiveM-Resource). */
function api(string $action, array $body, string $key = 'testkey'): array
{
    $ch = curl_init(TEST_BASE . '/api.php?action=' . $action);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => json_encode($body),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'X-API-Key: ' . $key]]);
    $res = (string) curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    return [$status, json_decode($res, true) ?: []];
}

/** Direkter Datenbankzugriff (für Vorbereitung und Prüfungen). */
function tdb(): PDO
{
    static $pdo = null;
    return $pdo ??= new PDO('sqlite:' . TEST_DB, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
}

function trow(string $sql, array $p = []): ?array
{
    $st = tdb()->prepare($sql);
    $st->execute($p);
    return $st->fetch() ?: null;
}

function tval(string $sql, array $p = [])
{
    $st = tdb()->prepare($sql);
    $st->execute($p);
    return $st->fetchColumn();
}

function texec(string $sql, array $p = []): void
{
    tdb()->prepare($sql)->execute($p);
}

/** Prüfung mit Ausgabe. */
function check(bool $cond, string $message): void
{
    $GLOBALS['test_count']++;
    if ($cond) {
        echo "  \033[32m✓\033[0m $message\n";
    } else {
        $GLOBALS['test_failed'][] = ($GLOBALS['test_current'] ?? '') . ': ' . $message;
        echo "  \033[31m✗ $message\033[0m\n";
    }
}

const TEST_SIGNATURE = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';
