<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error' => 'Método não permitido'], 405);
try {
    $d = request_json();
    if (!empty($d['website'])) json_response(['ok' => true]);
    $name = trim((string)($d['name'] ?? '')); $email = trim((string)($d['email'] ?? '')); $phone = trim((string)($d['phone'] ?? ''));
    $subject = trim((string)($d['subject'] ?? '')); $message = trim((string)($d['message'] ?? ''));
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $subject === '' || $message === '') json_response(['error' => 'Preencha nome, e-mail, assunto e mensagem.'], 422);
    if (empty($d['consent'])) json_response(['error' => 'É necessário aceitar a Política de Privacidade.'], 422);
    db()->prepare('INSERT INTO contacts (name,email,phone,subject,message,consent_at) VALUES (?,?,?,?,?,?)')->execute([$name,$email,$phone,$subject,$message,now_utc()]);
    json_response(['ok' => true, 'message' => 'Mensagem enviada com sucesso.']);
} catch (Throwable $e) { json_response(['error' => 'Não foi possível registrar a mensagem. Tente novamente mais tarde.'], 500); }
