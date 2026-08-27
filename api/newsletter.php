<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error' => 'Método não permitido'], 405);
try {
    $d = request_json();
    if (!empty($d['website'])) json_response(['ok' => true]);
    $email = trim((string)($d['email'] ?? ''));
    $source = trim((string)($d['source'] ?? 'site'));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_response(['error' => 'Informe um e-mail válido.'], 422);
    db()->prepare('INSERT INTO newsletter_subscribers (email, source) VALUES (?,?) ON CONFLICT (email) DO UPDATE SET status = \'active\'')->execute([$email, substr($source, 0, 120)]);
    json_response(['ok' => true, 'message' => 'Inscrição confirmada com sucesso.']);
} catch (Throwable $e) { json_response(['error' => 'Não foi possível concluir a inscrição. Tente novamente mais tarde.'], 500); }
