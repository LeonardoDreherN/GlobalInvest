<?php
require_once __DIR__ . '/../app/auth.php'; if(current_admin()){header('Location:/admin/dashboard.php');exit;} $error='';
const LOGIN_MAX_ATTEMPTS = 5; const LOGIN_WINDOW_MINUTES = 15;
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    $pdo = db();
    $s = $pdo->prepare("SELECT COUNT(*) c FROM login_attempts WHERE ip_address=? AND attempted_at > (NOW() - INTERVAL '" . LOGIN_WINDOW_MINUTES . " minutes')");
    $s->execute([$ip]); $ipAttempts = (int)$s->fetchColumn();
    $s = $pdo->prepare("SELECT COUNT(*) c FROM login_attempts WHERE email=? AND attempted_at > (NOW() - INTERVAL '" . LOGIN_WINDOW_MINUTES . " minutes')");
    $s->execute([$email]); $emailAttempts = (int)$s->fetchColumn();
    if ($ipAttempts >= LOGIN_MAX_ATTEMPTS || $emailAttempts >= LOGIN_MAX_ATTEMPTS) {
        $error = 'Muitas tentativas de acesso. Aguarde ' . LOGIN_WINDOW_MINUTES . ' minutos antes de tentar novamente.';
    } else {
        $s=$pdo->prepare('SELECT * FROM admins WHERE email=? AND is_active=1');$s->execute([$email]);$a=$s->fetch();
        if($a&&password_verify($_POST['password']??'',$a['password_hash'])){
            $pdo->prepare('DELETE FROM login_attempts WHERE email=? OR ip_address=?')->execute([$email,$ip]);
            session_regenerate_id(true);
            $_SESSION['admin']=['id'=>$a['id'],'name'=>$a['name'],'email'=>$a['email'],'role'=>$a['role']];header('Location:/admin/dashboard.php');exit;
        }
        $pdo->prepare('INSERT INTO login_attempts (email,ip_address) VALUES (?,?)')->execute([$email,$ip]);
        $error='E-mail ou senha inválidos.';
    }
}
?><!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Acesso administrativo | Global Invest Brasil</title><link rel="stylesheet" href="/admin/admin.css"></head><body><main class="login"><section class="card"><h1>Global Invest Brasil</h1><p class="muted">Acesso administrativo</p><?php if(isset($_GET['installed'])):?><p class="notice">Instalação concluída. Faça seu primeiro acesso.</p><?php endif;?><?php if($error):?><p class="error"><?=h($error)?></p><?php endif;?><form method="post"><input type="hidden" name="csrf" value="<?=csrf()?>"><label>E-mail<input type="email" name="email" required autofocus></label><label style="margin-top:15px">Senha<input type="password" name="password" required></label><button style="margin-top:18px">Entrar</button></form></section></main></body></html>
