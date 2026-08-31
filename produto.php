<?php
declare(strict_types=1);
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/markdown.php';

$slug = trim($_GET['slug'] ?? '');

try {
    $s = db()->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN product_categories c ON c.id = p.category_id WHERE p.slug = ? AND p.status = 'published'");
    $s->execute([$slug]);
    $p = $s->fetch();
    if (!$p) throw new RuntimeException('not found');
} catch (Throwable $e) {
    http_response_code(404);
    ?><!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>Produto não encontrado | Global Invest Brasil</title><link rel="stylesheet" href="/assets/css/styles.css?v=64"></head><body><main class="product-landing"><section class="section"><div class="container narrow"><a class="publication-back" href="/produtos.html">← Ver produtos</a><h1>Produto não encontrado</h1><p>O item que você procura não existe ou saiu do ar.</p></div></section></main></body></html><?php
    exit;
}

$title = (string) ($p['title'] ?? '');
$summary = (string) ($p['summary'] ?? '');
$category = (string) ($p['category_name'] ?? '');
$cta = (string) ($p['cta_label'] ?? '') ?: 'Quero saber mais';
$buyUrl = (string) ($p['purchase_url'] ?? '') ?: '/contato.html';
$canonical = base_url() . '/produto/' . rawurlencode($p['slug']);
$image = (string) ($p['image_url'] ?? '');
$ogImage = $image ?: (base_url() . '/assets/images/globalinvest-social.jpg');
$price = $p['price'] ?? null;
$priceLabel = ($price !== null && $price !== '' && (float) $price > 0)
    ? 'R$ ' . number_format((float) $price, 2, ',', '.')
    : '';
$bodyHtml = md_to_html($p['body'] ?? '');
$isExternal = (bool) preg_match('#^https?://#i', $buyUrl);
$ctaAttrs = $isExternal ? ' target="_blank" rel="noopener sponsored"' : '';
?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h($title) ?> | Global Invest Brasil</title>
<meta name="description" content="<?= h($summary) ?>">
<meta name="robots" content="index,follow,max-image-preview:large">
<meta name="theme-color" content="#07535a">
<link rel="canonical" href="<?= h($canonical) ?>">
<meta property="og:type" content="product">
<meta property="og:locale" content="pt_BR">
<meta property="og:site_name" content="Global Invest Brasil">
<meta property="og:title" content="<?= h($title) ?>">
<meta property="og:description" content="<?= h($summary) ?>">
<meta property="og:url" content="<?= h($canonical) ?>">
<meta property="og:image" content="<?= h($ogImage) ?>">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon-32x32.png" type="image/png" sizes="32x32">
<link rel="stylesheet" href="/assets/css/styles.css?v=64">
<script type="application/ld+json"><?= json_encode(array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $title,
    'description' => $summary,
    'image' => $ogImage,
    'brand' => ['@type' => 'Brand', 'name' => 'Global Invest Brasil'],
    'offers' => $priceLabel ? [
        '@type' => 'Offer',
        'price' => number_format((float) $price, 2, '.', ''),
        'priceCurrency' => 'BRL',
        'url' => $isExternal ? $buyUrl : $canonical,
        'availability' => 'https://schema.org/InStock',
    ] : null,
]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script src="/assets/js/main.js?v=63" defer></script>
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
<main id="conteudo" class="product-landing">
<section class="product-landing-hero">
<div class="container product-landing-hero-grid">
<div>
<a class="publication-back" href="/produtos.html">← Voltar aos produtos</a>
<span class="eyebrow"><?= h($category ?: 'Global Invest Brasil') ?></span>
<h1><?= h($title) ?></h1>
<?php if ($summary !== ''): ?><p><?= h($summary) ?></p><?php endif; ?>
<?php if ($priceLabel !== ''): ?><p class="product-landing-price"><strong><?= h($priceLabel) ?></strong></p><?php endif; ?>
<div class="product-landing-actions">
<a class="btn btn-primary" href="<?= h($buyUrl) ?>"<?= $ctaAttrs ?>><?= h($cta) ?></a>
<?php if ($bodyHtml !== ''): ?><a class="btn btn-secondary" href="#detalhes">Conheça os detalhes</a><?php endif; ?>
</div>
</div>
<div class="product-landing-visual">
<?php if ($image !== ''): ?><img class="product-landing-image" src="<?= h($image) ?>" alt="<?= h($title) ?>" width="1024" height="1536" decoding="async"><?php endif; ?>
</div>
</div>
</section>
<?php if ($bodyHtml !== ''): ?>
<section class="section product-landing-about" id="detalhes">
<div class="container narrow">
<div class="product-rich-text"><?= $bodyHtml ?></div>
<div class="product-landing-actions"><a class="btn btn-primary" href="<?= h($buyUrl) ?>"<?= $ctaAttrs ?>><?= h($cta) ?></a></div>
</div>
</section>
<?php endif; ?>
</main>
<footer class="site-footer">
<div class="container">
<p class="footer-legal-line">Global Invest Brasil - Todos os direitos reservados.</p>
</div>
</footer>
</body>
</html>
