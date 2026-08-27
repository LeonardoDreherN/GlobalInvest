<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error' => 'Método não permitido'], 405);
try {
    $d = request_json();
    $preferences = in_array($d['preferences'] ?? '', ['accepted_all', 'rejected_optional'], true) ? $d['preferences'] : 'accepted_all';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    db()->prepare('INSERT INTO cookie_consents (ip_address, preferences) VALUES (?,?)')->execute([$ip, $preferences]);
    json_response(['ok' => true]);
} catch (Throwable $e) { json_response(['ok' => true]); }
