<?php
require_once __DIR__ . '/../app/auth.php';

function admin_current_site(string $path): string {
    if (in_array($path, ['jd-catalog.php', 'jd-catalog-edit.php'], true)) return 'jorgedadalt';
    if ($path === 'manage.php' && ($_GET['entity'] ?? '') === 'contacts' && ($_GET['site'] ?? '') === 'jorgedadalt') return 'jorgedadalt';
    return 'gib';
}

function admin_icon(string $name): string {
    $paths = [
        'home' => '<path d="M4 11.5 12 4l8 7.5"/><path d="M6 10v9h5v-5h2v5h5v-9"/>',
        'box' => '<path d="M3 8l9-4 9 4-9 4-9-4Z"/><path d="M3 8v8l9 4 9-4V8"/><path d="M12 12v8"/>',
        'file-text' => '<path d="M7 3h7l5 5v13H7Z"/><path d="M14 3v5h5"/><path d="M9.5 13h6M9.5 16.5h6"/>',
        'edit' => '<path d="M4 20l4-1 11-11-3-3L5 16l-1 4Z"/><path d="M14 6l3 3"/>',
        'tag' => '<path d="M11 3H4v7l10 10 7-7L11 3Z"/><circle cx="8" cy="8" r="1.3"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M4 6.5l8 6.5 8-6.5"/>',
        'clipboard' => '<rect x="6" y="4" width="12" height="17" rx="2"/><rect x="9" y="2.3" width="6" height="3.4" rx="1"/><path d="M9 11h6M9 15h6"/>',
        'book' => '<path d="M12 6c-1.7-1.4-4-2-7-2v13c3 0 5.3.6 7 2 1.7-1.4 4-2 7-2V4c-3 0-5.3.6-7 2Z"/><path d="M12 6v13"/>',
        'sliders' => '<circle cx="16" cy="6" r="2"/><path d="M4 6h10M18 6h2"/><circle cx="8" cy="12" r="2"/><path d="M4 12h2M10 12h10"/><circle cx="18" cy="18" r="2"/><path d="M4 18h12M20 18h0"/>',
        'external-link' => '<path d="M9 6H5a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-4"/><path d="M14 4h6v6"/><path d="M10 14 20 4"/>',
        'log-out' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/>',
        'building' => '<rect x="4" y="3" width="10" height="18" rx="1"/><rect x="14" y="9" width="6" height="12" rx="1"/><path d="M7 7h1M7 11h1M7 15h1M10 7h1M10 11h1M10 15h1"/>',
        'user' => '<circle cx="12" cy="8" r="3.6"/><path d="M4.5 20c1.4-4 4-6 7.5-6s6.1 2 7.5 6"/>',
        'trending-up' => '<path d="M3 17l6-6 4 4 8-8"/><path d="M15 6h6v6"/>',
        'trending-down' => '<path d="M3 7l6 6 4-4 8 8"/><path d="M21 12v6h-6"/>',
        'bar-chart' => '<path d="M4 20V10M10 20V4M16 20v-7M2 20h20"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
        'plus-circle' => '<circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/>',
        'arrow-right' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'users' => '<circle cx="9" cy="8" r="3.2"/><path d="M2.5 20c1.1-3.5 3.4-5.3 6.5-5.3s5.4 1.8 6.5 5.3"/><path d="M16 4.8c1.6.4 2.8 1.8 2.8 3.5 0 1.6-1.1 3-2.6 3.4"/><path d="M18.5 14.8c2 .6 3.4 2.2 4 4.7"/>',
        'target' => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.3"/>',
    ];
    $inner = $paths[$name] ?? '';
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $inner . '</svg>';
}

function admin_initials(string $name): string {
    $parts = preg_split('/\s+/', trim($name));
    $letters = array_map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)), array_slice($parts, 0, 2));
    return implode('', $letters) ?: '?';
}

function admin_role_label(string $role): string {
    return ['administrator' => 'Administrador', 'editor' => 'Editor'][$role] ?? ucfirst($role);
}

function admin_header(string $title): void { $a=require_admin(); $path=basename($_SERVER['PHP_SELF']); $site=admin_current_site($path); ?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=h($title)?> | Administração</title><link rel="stylesheet" href="/admin/admin.css"></head><body><div class="layout"><aside class="side">
<div class="brand"><span class="brand-mark">GI</span><span class="brand-text">Global <b class="brand-accent">Invest</b><br>Brasil</span></div>

<div class="site-switcher" role="tablist">
  <a class="site-tab <?=$site==='gib'?'active':''?>" href="/admin/dashboard.php"><?=admin_icon('building')?><span>Global Invest</span></a>
  <a class="site-tab <?=$site==='jorgedadalt'?'active':''?>" href="/admin/jd-catalog.php"><?=admin_icon('user')?><span>Jorge Dadalt</span></a>
</div>

<nav class="nav">
<?php if ($site === 'gib'): ?>
<span class="nav-group-label">Global Invest Brasil</span>
<a class="<?=$path==='dashboard.php'?'on':''?>" href="/admin/dashboard.php"><?=admin_icon('home')?>Painel principal</a>
<a class="<?=$path==='manage.php'&&($_GET['entity']??'')==='products'?'on':''?>" href="/admin/manage.php?entity=products"><?=admin_icon('box')?>Produtos</a>
<a class="<?=$path==='manage.php'&&($_GET['entity']??'')==='publications'?'on':''?>" href="/admin/manage.php?entity=publications"><?=admin_icon('file-text')?>Publicações</a>
<a class="<?=$path==='manage.php'&&($_GET['entity']??'')==='blog_posts'?'on':''?>" href="/admin/manage.php?entity=blog_posts"><?=admin_icon('edit')?>Blog</a>
<a class="<?=$path==='categories.php'?'on':''?>" href="/admin/categories.php"><?=admin_icon('tag')?>Categorias</a>
<a class="<?=$path==='manage.php'&&($_GET['entity']??'')==='contacts'&&($_GET['site']??'')!=='jorgedadalt'?'on':''?>" href="/admin/manage.php?entity=contacts&site=gib"><?=admin_icon('mail')?>Contatos</a>
<a class="<?=$path==='briefings.php'||$path==='briefing-view.php'?'on':''?>" href="/admin/briefings.php"><?=admin_icon('clipboard')?>Formulários de projeto</a>
<span class="nav-group-label">Sistema</span>
<a class="<?=$path==='settings.php'?'on':''?>" href="/admin/settings.php"><?=admin_icon('sliders')?>SEO e AdSense</a>
<div class="nav-external"><a href="/" target="_blank"><?=admin_icon('external-link')?>Visualizar site ↗</a></div>
<?php else: ?>
<span class="nav-group-label">Professor Jorge Dadalt</span>
<a class="<?=$path==='jd-catalog.php'||$path==='jd-catalog-edit.php'?'on':''?>" href="/admin/jd-catalog.php"><?=admin_icon('book')?>Livros, cursos e soluções</a>
<a class="<?=$path==='manage.php'&&($_GET['entity']??'')==='contacts'&&($_GET['site']??'')==='jorgedadalt'?'on':''?>" href="/admin/manage.php?entity=contacts&site=jorgedadalt"><?=admin_icon('mail')?>Contatos</a>
<div class="nav-external"><a href="https://www.jorgedadalt.com" target="_blank" rel="noopener"><?=admin_icon('external-link')?>Visualizar site ↗</a></div>
<?php endif; ?>
</nav>
<div style="margin-top:10px;border-top:1px solid rgba(255,255,255,.09);padding-top:10px">
<a href="/admin/logout.php"><?=admin_icon('log-out')?>Sair</a>
</div>
</aside><main class="main"><div class="topbar"><h1><?=h($title)?></h1><div class="topbar-user"><div class="avatar"><?=h(admin_initials($a['name']))?></div><div class="who"><b><?=h($a['name'])?></b><span><?=h(admin_role_label($a['role'] ?? 'administrator'))?></span></div></div></div>
<?php }
function admin_footer(): void { echo '</main></div></body></html>'; }
