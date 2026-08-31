<?php
declare(strict_types=1);
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/markdown.php';

$slug = trim($_GET['slug'] ?? '');

// Produtos com landing page própria (estática).
if ($slug === 'produto-ideia-ao-lucro') { header('Location: /da-ideia-ao-lucro/', true, 301); exit; }

try {
    $s = db()->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN product_categories c ON c.id = p.category_id WHERE p.slug = ? AND p.status = 'published'");
    $s->execute([$slug]);
    $p = $s->fetch();
    if (!$p) throw new RuntimeException('not found');
} catch (Throwable $e) {
    http_response_code(404);
    ?><!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>Produto não encontrado | Global Invest Brasil</title><link rel="stylesheet" href="/assets/css/styles.css?v=72"></head><body><main class="product-page"><div class="product-layout container"><div><a class="publication-back" href="/produtos.html">← Ver produtos</a><h1>Produto não encontrado</h1><p>O item que você procura não existe ou saiu do ar.</p></div></div></main></body></html><?php
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
$isExternal = (bool) preg_match('#^https?://#i', $buyUrl);
$ctaAttrs = $isExternal ? ' target="_blank" rel="noopener sponsored"' : '';
$buyNote = $isExternal ? 'Compra pelo checkout oficial da loja' : 'Atendimento direto pela equipe Global Invest Brasil';

$bodyHtml = md_to_html($p['body'] ?? '');
// Perguntas frequentes: "<h2>...?</h2><p>...</p>" vira accordion <details>.
$bodyHtml = preg_replace_callback(
    '#<h2>([^<]*\?)</h2>\s*<p>(.*?)</p>#is',
    fn($m) => '<details class="product-faq"><summary>' . trim($m[1]) . '</summary><p>' . $m[2] . '</p></details>',
    $bodyHtml
);
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
<link rel="stylesheet" href="/assets/css/styles.css?v=72">
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
<script src="/assets/js/main.js?v=72" defer></script>
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
<main id="conteudo" class="product-page">

<section class="product-hero">
<div class="container product-hero-inner">
<div class="product-hero-text">
<a class="publication-back" href="/produtos.html">← Voltar aos produtos</a>
<?php if ($category !== ''): ?><span class="eyebrow"><?= h($category) ?></span><?php endif; ?>
<h1><?= h($title) ?></h1>
<?php if ($summary !== ''): ?><p class="product-hero-lead"><?= h($summary) ?></p><?php endif; ?>
<div class="product-hero-actions">
<a class="btn btn-primary" href="<?= h($buyUrl) ?>"<?= $ctaAttrs ?>><?= h($cta) ?></a>
<?php if ($bodyHtml !== ''): ?><a class="btn btn-ghost" href="#detalhes">Ver detalhes</a><?php endif; ?>
</div>
</div>
<?php if ($image !== ''): ?>
<figure class="product-hero-media"><img src="<?= h($image) ?>" alt="<?= h($title) ?>" decoding="async" fetchpriority="high"></figure>
<?php endif; ?>
</div>
</section>

<div class="container product-layout">
<article class="product-article" id="detalhes">
<?php if ($bodyHtml !== ''): ?><?= $bodyHtml ?><?php else: ?><p><?= h($summary) ?></p><?php endif; ?>
</article>

<aside class="product-buybox">
<div class="product-buybox-card">
<?php if ($image !== ''): ?><img class="product-buybox-thumb" src="<?= h($image) ?>" alt="" loading="lazy"><?php endif; ?>
<?php if ($category !== ''): ?><span class="product-buybox-cat"><?= h($category) ?></span><?php endif; ?>
<strong class="product-buybox-title"><?= h($title) ?></strong>
<?php if ($priceLabel !== ''): ?><span class="product-buybox-price"><?= h($priceLabel) ?></span><?php endif; ?>
<a class="btn btn-primary btn-block" href="<?= h($buyUrl) ?>"<?= $ctaAttrs ?>><?= h($cta) ?></a>
<ul class="product-buybox-notes">
<li><?= h($buyNote) ?></li>
</ul>
</div>
</aside>
</div>

<section class="product-cta-final">
<div class="container narrow">
<span class="kicker">Próximo passo</span>
<h2><?= h($title) ?></h2>
<p>Converse com a Global Invest Brasil e entenda como esta solução se encaixa no seu momento.</p>
<a class="btn btn-primary" href="<?= h($buyUrl) ?>"<?= $ctaAttrs ?>><?= h($cta) ?></a>
<?php if ($priceLabel !== ''): ?><span class="cta-price"><?= h($priceLabel) ?></span><?php endif; ?>
</div>
</section>
</main>
<footer class="site-footer">
<div class="container">
<p class="footer-legal-line">Global Invest Brasil - Todos os direitos reservados.</p>
</div>
</footer>
</body>
</html>
