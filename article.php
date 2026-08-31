<?php
declare(strict_types=1);
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/markdown.php';

$isBlog = ($_GET['type'] ?? '') === 'blog';
$table = $isBlog ? 'blog_posts' : 'publications';
$catTable = $isBlog ? 'blog_categories' : 'publication_categories';
$slug = trim($_GET['slug'] ?? '');

try {
    $s = db()->prepare("SELECT p.*, c.name AS category_name FROM {$table} p LEFT JOIN {$catTable} c ON c.id = p.category_id WHERE p.slug = ? AND p.status = 'published'");
    $s->execute([$slug]);
    $p = $s->fetch();
    if (!$p) throw new RuntimeException('not found');
} catch (Throwable $e) {
    http_response_code(404);
    ?><!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>Conteúdo não encontrado | Global Invest Brasil</title><link rel="stylesheet" href="/assets/css/styles.css?v=70"></head><body><main class="publication-page"><div class="container publication-article"><a class="publication-back" href="/">← Início</a><header class="publication-heading"><h1>Conteúdo não encontrado</h1></header><p class="publication-deck">A publicação que você procura não existe, saiu do ar ou o endereço está incorreto.</p><p><a class="publication-back" href="/publicacoes.html">Ver publicações</a></p></div></main></body></html><?php
    exit;
}

function data_extenso(?string $ts): array {
    $t = $ts ? strtotime($ts) : false;
    if (!$t) return ['', ''];
    $meses = [1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril', 5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto', 9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro'];
    return [date('Y-m-d', $t), (int) date('j', $t) . ' de ' . $meses[(int) date('n', $t)] . ' de ' . date('Y', $t)];
}

[$dateAttr, $dateLabel] = data_extenso($p['published_at'] ?? ($p['created_at'] ?? null));
$title = (string) ($p['title'] ?? '');
$pageTitle = (string) (($p['seo_title'] ?? '') ?: $title);
$desc = (string) (($p['seo_description'] ?? '') ?: ($p['excerpt'] ?? ''));
$category = (string) ($p['category_name'] ?? '');
$backHref = $isBlog ? '/artigos.html' : '/publicacoes.html';
$backLabel = $isBlog ? '← Voltar ao blog' : '← Voltar às publicações';
$canonical = base_url() . ($isBlog ? '/blog/' : '/publicacao/') . rawurlencode($p['slug']);
$ogImage = (string) ($p['image_url'] ?? '') ?: (base_url() . '/assets/images/globalinvest-social.jpg');
$bodyHtml = md_to_html($p['content'] ?? '');
?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h($pageTitle) ?> | Global Invest Brasil</title>
<meta name="description" content="<?= h($desc) ?>">
<meta name="robots" content="index,follow,max-image-preview:large">
<meta name="theme-color" content="#07535a">
<link rel="canonical" href="<?= h($canonical) ?>">
<meta property="og:type" content="article">
<meta property="og:locale" content="pt_BR">
<meta property="og:site_name" content="Global Invest Brasil">
<meta property="og:title" content="<?= h($pageTitle) ?>">
<meta property="og:description" content="<?= h($desc) ?>">
<meta property="og:url" content="<?= h($canonical) ?>">
<meta property="og:image" content="<?= h($ogImage) ?>">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon-32x32.png" type="image/png" sizes="32x32">
<link rel="stylesheet" href="/assets/css/styles.css?v=70">
<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => $title,
    'description' => $desc,
    'inLanguage' => 'pt-BR',
    'datePublished' => $dateAttr,
    'image' => $ogImage,
    'author' => ['@type' => 'Organization', 'name' => (string) ($p['author_name'] ?? 'Global Invest Brasil')],
    'publisher' => ['@type' => 'Organization', 'name' => 'Global Invest Brasil', 'logo' => ['@type' => 'ImageObject', 'url' => base_url() . '/assets/images/logo-globalinvestbr-circular.png']],
    'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonical],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script src="/assets/js/main.js?v=70" defer></script>
<script src="/adsense-loader.php" defer></script>
</head>
<body data-content-server-rendered="true">
<a class="skip-link" href="#conteudo">Ir para o conteúdo</a>
<header class="site-header">
<nav class="nav container" aria-label="Navegação principal">
<a class="brand" href="/index.html" aria-label="Global Invest Brasil - início">
<img class="home-brand-symbol" src="/assets/images/logo-globalinvestbr-circular.png" alt="" width="106" height="106">
<strong class="home-brand-name home-brand-lines" aria-label="Global Invest Brasil"><span class="home-brand-top"><span>Global</span><em>Invest</em></span><span class="home-brand-bottom">Brasil</span></strong>
</a>
<button class="menu-toggle" type="button" data-menu-toggle aria-expanded="false" aria-controls="menu">☰</button>
<ul class="nav-links" id="menu" data-nav-links></ul>
</nav>
</header>
<main id="conteudo" class="publication-page">
<article class="container publication-article">
<a class="publication-back" href="<?= h($backHref) ?>"><?= h($backLabel) ?></a>
<header class="publication-heading">
<?php if ($category !== ''): ?><span><?= h($category) ?></span><?php endif; ?>
<h1><?= h($title) ?></h1>
<?php if ($dateLabel !== ''): ?><time datetime="<?= h($dateAttr) ?>"><?= h($dateLabel) ?></time><?php endif; ?>
</header>
<?php if ($desc !== ''): ?><p class="publication-deck"><?= h($desc) ?></p><?php endif; ?>
<div class="publication-body">
<?php if (!empty($p['image_url'])): ?>
<figure class="publication-editorial-image"><img src="<?= h($p['image_url']) ?>" alt="<?= h(($p['image_alt'] ?? '') ?: $title) ?>"><?php if (!empty($p['image_alt'])): ?><figcaption><?= h($p['image_alt']) ?></figcaption><?php endif; ?></figure>
<?php endif; ?>
<?= $bodyHtml ?>
</div>
</article>
</main>
<footer class="site-footer">
<div class="container">
<p class="footer-legal-line">Global Invest Brasil - Todos os direitos reservados.</p>
</div>
</footer>
</body>
</html>
