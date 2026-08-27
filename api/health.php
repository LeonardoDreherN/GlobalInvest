<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';
try { db()->query('SELECT 1'); json_response(['ok' => true, 'database' => 'connected', 'timestamp' => gmdate(DATE_ATOM)]); }
catch (Throwable $e) { json_response(['ok' => false, 'database' => 'unavailable', 'message' => 'Verifique as credenciais em app/config.php.'], 503); }
