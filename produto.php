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
    ?><!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>Produto não encontrado | Global Invest Brasil</title><link rel="stylesheet" href="/assets/css/styles.css?v=72"></head><body><main style="max-width:640px;margin:80px auto;padding:0 24px"><a href="/produtos.html">← Ver produtos</a><h1>Produto não encontrado</h1><p>O item que você procura não existe ou saiu do ar.</p></main></body></html><?php
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
$buyNote = $isExternal ? 'Compra pelo checkout oficial da loja · acesso imediato' : 'Atendimento direto pela equipe Global Invest Brasil';

$bodyHtml = md_to_html($p['body'] ?? '');
// "<h2>...?</h2><p>...</p>" vira item de FAQ (separado do corpo).
$faq = '';
$bodyHtml = preg_replace_callback(
    '#<h2>([^<]*\?)</h2>\s*<p>(.*?)</p>#is',
    function ($m) use (&$faq) {
        $faq .= '<details><summary>' . trim($m[1]) . '</summary><p>' . $m[2] . '</p></details>';
        return '';
    },
    $bodyHtml
);
$blocks = array_values(array_filter(array_map('trim', preg_split('/(?=<h2>)/', $bodyHtml)), 'strlen'));

function pl_buy(string $cls, string $url, string $attrs, string $label): string {
    return '<a class="pl-btn' . ($cls ? ' ' . $cls : '') . '" href="' . h($url) . '"' . $attrs . '>' . h($label) . '</a>';
}
?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h($title) ?> | Global Invest Brasil</title>
<meta name="description" content="<?= h($summary) ?>">
<meta name="robots" content="index,follow,max-image-preview:large">
<meta name="theme-color" content="#06363a">
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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@700;800;900&family=Inter+Tight:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/styles.css?v=72">
<link rel="stylesheet" href="/assets/css/produto-landing.css?v=1">
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

<section class="pl-hero">
<div class="pl-wrap pl-hero-in">
<div>
<a class="pl-back" href="/produtos.html">&larr; Voltar aos produtos</a>
<?php if ($category !== ''): ?><span class="pl-eyebrow"><?= h($category) ?></span><?php endif; ?>
<h1><?= h($title) ?></h1>
<?php if ($summary !== ''): ?><p class="pl-hero-lead"><?= h($summary) ?></p><?php endif; ?>
<?= pl_buy('', $buyUrl, $ctaAttrs, $cta) ?>
<p class="pl-note"><?= h($buyNote) ?></p>
</div>
<?php if ($image !== ''): ?><div class="pl-hero-media"><img src="<?= h($image) ?>" alt="<?= h($title) ?>" decoding="async" fetchpriority="high"></div><?php endif; ?>
</div>
</section>

<?php foreach ($blocks as $i => $block): ?>
<section class="pl-sec <?= $i % 2 ? 'pl-sec--ivory' : 'pl-sec--paper' ?>"><div class="pl-wrap pl-body pl-reveal"><?= $block ?></div></section>
<?php endforeach; ?>

<section class="pl-offer-sec">
<div class="pl-wrap">
<div class="pl-offer pl-reveal">
<div class="pl-offer__t"><?= $isExternal ? 'Acesso imediato &middot; Compra segura' : 'Fale com a equipe Global Invest Brasil' ?></div>
<div class="pl-offer__b">
<h2><?= h($title) ?></h2>
<?php if ($summary !== ''): ?><p><?= h($summary) ?></p><?php endif; ?>
<?php if ($priceLabel !== ''): ?><div class="pl-price"><?= h($priceLabel) ?></div><?php endif; ?>
<div class="pl-offer__cta"><?= pl_buy('', $buyUrl, $ctaAttrs, $cta) ?></div>
<?php if ($isExternal): ?><div class="pl-seals"><span>Pagamento seguro</span><span>Acesso imediato por e-mail</span><span>Garantia de 7 dias</span></div><?php endif; ?>
</div>
</div>
</div>
</section>

<?php if ($faq !== ''): ?>
<section class="pl-sec pl-sec--ivory"><div class="pl-wrap pl-body pl-reveal">
<span class="pl-eyebrow">Dúvidas frequentes</span>
<h2>Perguntas frequentes</h2>
<div class="pl-faq"><?= $faq ?></div>
</div></section>
<?php endif; ?>

<section class="pl-sec pl-sec--deep">
<div class="pl-wrap pl-final pl-reveal">
<span class="pl-eyebrow">Próximo passo</span>
<h2><?= h($title) ?></h2>
<p>Converse com a Global Invest Brasil e veja como esta solução se encaixa no seu momento.</p>
<?= pl_buy('', $buyUrl, $ctaAttrs, $cta) ?>
</div>
</section>

</main>

<div class="pl-buybar">
<div class="pl-buybar__p"><b><?= $priceLabel !== '' ? h($priceLabel) : 'Global Invest Brasil' ?></b><small><?= h($category ?: 'Produto') ?></small></div>
<?= pl_buy('', $buyUrl, $ctaAttrs, $cta) ?>
</div>

<footer class="site-footer">
<div class="container">
<p class="footer-legal-line">Global Invest Brasil - Todos os direitos reservados.</p>
</div>
</footer>

<script>
(function(){
  var io=new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting){e.target.classList.add('is-in');io.unobserve(e.target);}});},{threshold:.1,rootMargin:'0px 0px -40px 0px'});
  document.querySelectorAll('.pl-reveal').forEach(function(el){io.observe(el);});
})();
</script>
</body>
</html>
