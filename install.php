<?php
declare(strict_types=1);
require_once __DIR__ . '/app/bootstrap.php';
function e(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

$envMode = getenv('DB_HOST') !== false;
$configFile = __DIR__ . '/app/config.php';

function already_installed(bool $envMode, string $configFile): bool {
    if (!$envMode && is_file($configFile)) return true;
    try {
        $count = db()->query('SELECT COUNT(*) FROM admins')->fetchColumn();
        return (int)$count > 0;
    } catch (Throwable $e) { return false; }
}

if (already_installed($envMode, $configFile)) { http_response_code(403); exit('Instalação já concluída. Por segurança, remova install.php depois de testar o painel.'); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminName = trim($_POST['admin_name'] ?? ''); $adminEmail = trim($_POST['admin_email'] ?? ''); $adminPass = (string)($_POST['admin_password'] ?? ''); $confirm = (string)($_POST['admin_password_confirm'] ?? '');
    try {
        if (!$adminName || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL) || strlen($adminPass) < 10 || $adminPass !== $confirm) throw new RuntimeException('Revise os campos. A senha administrativa deve ter ao menos 10 caracteres e as senhas devem coincidir.');

        if ($envMode) {
            $pdo = db();
        } else {
            $host = trim($_POST['db_host'] ?? 'localhost'); $port = trim($_POST['db_port'] ?? '5432'); $name = trim($_POST['db_name'] ?? ''); $user = trim($_POST['db_user'] ?? ''); $pass = (string)($_POST['db_password'] ?? '');
            if (!$name || !$user || !$pass) throw new RuntimeException('Preencha os dados de conexão do banco.');
            $pdo = new PDO("pgsql:host={$host};port={$port};dbname={$name};sslmode=require", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        }

        $sql = file_get_contents(__DIR__ . '/database/globalinvestbr-schema.sql');
        if ($sql === false) throw new RuntimeException('Arquivo do banco não encontrado. Reextraia o pacote completo.');
        $sql = preg_replace('/^\s*--.*$/m', '', $sql);
        foreach (preg_split('/;\s*(?:\r?\n|$)/', $sql) as $statement) { if (trim($statement) !== '') $pdo->exec($statement); }

        $check = $pdo->prepare('SELECT COUNT(*) FROM admins WHERE email = ?'); $check->execute([$adminEmail]);
        if ((int)$check->fetchColumn() === 0) $pdo->prepare('INSERT INTO admins (name,email,password_hash,role) VALUES (?,?,?,?)')->execute([$adminName, $adminEmail, password_hash($adminPass, PASSWORD_DEFAULT), 'administrator']);

        if (!$envMode) {
            $siteUrl = 'https://' . preg_replace('/[^a-z0-9.\-]/i', '', $_SERVER['HTTP_HOST'] ?? 'globalinvestbrasil.com');
            $config = "<?php\nreturn " . var_export(['db_host'=>$host,'db_port'=>$port,'db_name'=>$name,'db_user'=>$user,'db_password'=>$pass,'db_sslmode'=>'require','site_url'=>$siteUrl], true) . ";\n";
            if (file_put_contents($configFile, $config, LOCK_EX) === false) throw new RuntimeException('Não foi possível salvar app/config.php. Verifique a permissão da pasta app.');
        }
        header('Location: /admin/login.php?installed=1'); exit;
    } catch (Throwable $e) { $error = $e->getMessage(); }
}
?><!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Instalação | Global Invest Brasil</title><style>body{font-family:Arial,sans-serif;background:#063b3c;margin:0;padding:32px;color:#152233}.box{max-width:760px;background:#fff;border-radius:18px;margin:auto;padding:34px;box-shadow:0 15px 40px #0005}h1{margin-top:0}.grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}label{font-weight:700;display:block}input{box-sizing:border-box;width:100%;padding:12px;margin-top:6px;border:1px solid #cbd5d9;border-radius:8px}button{margin-top:24px;background:#e96543;color:#fff;border:0;border-radius:8px;padding:14px 20px;font-weight:700;font-size:16px}.err{background:#fee2e2;color:#991b1b;padding:12px;border-radius:8px}.notice{background:#e0f2f1;color:#0b4a47;padding:12px;border-radius:8px}@media(max-width:640px){.grid{grid-template-columns:1fr}}</style></head><body><main class="box"><h1>Configuração inicial</h1><p>Os dados são enviados somente ao seu servidor. Não compartilhe senhas no chat.</p><?php if($error): ?><p class="err"><?=e($error)?></p><?php endif; ?><?php if($envMode): ?><p class="notice">Conexão com o banco detectada por variáveis de ambiente (DB_HOST). Preencha apenas os dados do administrador.</p><?php endif; ?><form method="post"><?php if(!$envMode): ?><div class="grid"><label>Servidor PostgreSQL<input name="db_host" value="localhost" required></label><label>Porta<input name="db_port" value="5432" required></label><label>Nome do banco<input name="db_name" required placeholder="postgres"></label><label>Usuário do banco<input name="db_user" required placeholder="postgres"></label></div><label>Senha do banco<input type="password" name="db_password" required></label><hr><?php endif; ?><div class="grid"><label>Nome do administrador<input name="admin_name" value="Professor Jorge Dadalt" required></label><label>E-mail administrativo<input type="email" name="admin_email" required></label><label>Senha administrativa<input type="password" name="admin_password" minlength="10" required></label><label>Confirmar senha<input type="password" name="admin_password_confirm" minlength="10" required></label></div><button>Concluir instalação</button></form></main></body></html>
