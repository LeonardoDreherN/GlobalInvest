<?php
require_once __DIR__ . '/../app/auth.php';
function admin_current_site(string $path): string {
    if (in_array($path, ['jd-catalog.php', 'jd-catalog-edit.php'], true)) return 'jorgedadalt';
    if ($path === 'manage.php' && ($_GET['entity'] ?? '') === 'contacts' && ($_GET['site'] ?? '') === 'jorgedadalt') return 'jorgedadalt';
    return 'gib';
}
function admin_header(string $title): void { $a=require_admin(); $path=basename($_SERVER['PHP_SELF']); $site=admin_current_site($path); ?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=h($title)?> | Administração</title><link rel="stylesheet" href="/admin/admin.css"></head><body><div class="layout"><aside class="side"><div class="brand">Global <span>Invest</span><br>Brasil<small style="display:block;font-size:11px;letter-spacing:1.5px;margin-top:6px">CENTRAL DE CONTEÚDO</small></div>
<div class="site-switcher">
  <label for="site-select">Você está editando</label>
  <select id="site-select" onchange="location.href=this.value">
    <option value="/admin/dashboard.php" <?=$site==='gib'?'selected':''?>>🏢 Global Invest Brasil</option>
    <option value="/admin/jd-catalog.php" <?=$site==='jorgedadalt'?'selected':''?>>📘 Professor Jorge Dadalt</option>
  </select>
</div>
<nav class="nav">
<?php if ($site === 'gib'): ?>
<span class="nav-group-label">Global Invest Brasil</span>
<a class="<?=$path==='dashboard.php'?'on':''?>" href="/admin/dashboard.php">Painel principal</a>
<a class="<?=$path==='manage.php'&&($_GET['entity']??'')==='products'?'on':''?>" href="/admin/manage.php?entity=products">Produtos</a>
<a class="<?=$path==='manage.php'&&($_GET['entity']??'')==='publications'?'on':''?>" href="/admin/manage.php?entity=publications">Publicações</a>
<a class="<?=$path==='manage.php'&&($_GET['entity']??'')==='blog_posts'?'on':''?>" href="/admin/manage.php?entity=blog_posts">Blog</a>
<a class="<?=$path==='categories.php'?'on':''?>" href="/admin/categories.php">Categorias</a>
<a class="<?=$path==='manage.php'&&($_GET['entity']??'')==='contacts'&&($_GET['site']??'')!=='jorgedadalt'?'on':''?>" href="/admin/manage.php?entity=contacts&site=gib">Contatos</a>
<a class="<?=$path==='briefings.php'||$path==='briefing-view.php'?'on':''?>" href="/admin/briefings.php">Formulários de projeto</a>
<span class="nav-group-label">Sistema</span>
<a class="<?=$path==='settings.php'?'on':''?>" href="/admin/settings.php">SEO e AdSense</a>
<a href="/" target="_blank">Visualizar site ↗</a>
<?php else: ?>
<span class="nav-group-label">Professor Jorge Dadalt</span>
<a class="<?=$path==='jd-catalog.php'||$path==='jd-catalog-edit.php'?'on':''?>" href="/admin/jd-catalog.php">Livros, cursos e soluções</a>
<a class="<?=$path==='manage.php'&&($_GET['entity']??'')==='contacts'&&($_GET['site']??'')==='jorgedadalt'?'on':''?>" href="/admin/manage.php?entity=contacts&site=jorgedadalt">Contatos</a>
<a href="https://www.jorgedadalt.com" target="_blank" rel="noopener">Visualizar site ↗</a>
<?php endif; ?>
<span class="nav-group-label">Conta</span>
<a href="/admin/logout.php">Sair</a>
</nav></aside><main class="main"><div class="topbar"><h1><?=h($title)?></h1><p class="muted">Olá, <?=h($a['name'])?></p></div>
<?php }
function admin_footer(): void { echo '</main></div></body></html>'; }
