<?php
declare(strict_types=1);

const APP_ROOT = __DIR__ . '/..';
const APP_CONFIG = __DIR__ . '/config.php';

function app_config(): array {
    static $config = null;
    if ($config !== null) return $config;
    $envHost = getenv('DB_HOST');
    if ($envHost !== false) {
        $config = [
            'db_host' => $envHost,
            'db_port' => getenv('DB_PORT') ?: '5432',
            'db_name' => getenv('DB_NAME') ?: 'postgres',
            'db_user' => getenv('DB_USER') ?: '',
            'db_password' => getenv('DB_PASSWORD') ?: '',
            'db_sslmode' => getenv('DB_SSLMODE') ?: 'require',
            'site_url' => getenv('SITE_URL') ?: '',
        ];
        return $config;
    }
    if (!is_file(APP_CONFIG)) throw new RuntimeException('Sistema não instalado. Acesse /install.php ou configure as variáveis de ambiente DB_HOST, DB_NAME, DB_USER, DB_PASSWORD.');
    $config = require APP_CONFIG;
    return $config;
}

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    $c = app_config();
    $dsn = 'pgsql:host=' . $c['db_host'] . ';port=' . ($c['db_port'] ?? '5432') . ';dbname=' . $c['db_name'] . ';sslmode=' . ($c['db_sslmode'] ?? 'require');
    $pdo = new PDO($dsn, $c['db_user'], $c['db_password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}

function h(?string $value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function now_utc(): string { return gmdate('Y-m-d H:i:s'); }
function setting(string $key, string $default = ''): string {
    try { $s = db()->prepare('SELECT setting_value FROM site_settings WHERE setting_key = ?'); $s->execute([$key]); return (string)($s->fetchColumn() ?: $default); }
    catch (Throwable $e) { return $default; }
}
function json_response(array $data, int $status = 200): never {
    http_response_code($status); header('Content-Type: application/json; charset=utf-8'); header('Cache-Control: no-store'); echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); exit;
}
function request_json(): array {
    $raw = file_get_contents('php://input'); $data = json_decode($raw ?: '{}', true);
    return is_array($data) ? $data : $_POST;
}
function base_url(): string { return rtrim(app_config()['site_url'] ?? '', '/'); }
