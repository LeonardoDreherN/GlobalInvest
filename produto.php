<?php
declare(strict_types=1);
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/markdown.php';
require_once __DIR__ . '/app/site-shell.php';

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
    ?><!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>Produto não encontrado | Global Invest Brasil</title><link rel="stylesheet" href="/assets/css/site.css?v=<?= SITE_ASSET_VERSION ?>"><script src="/assets/js/site.js?v=<?= SITE_ASSET_VERSION ?>" defer></script></head><body><?= site_header() ?><main id="conteudo"><section class="page-hero"><div class="wrap"><p class="eyebrow">Produto não encontrado</p><h1 class="h2" style="margin-top:22px">Este item não existe ou saiu do ar.</h1><div class="btn-row"><a class="btn" href="/solucoes.html">Ver soluções</a></div></div></section></main><?= site_footer() ?></body></html><?php
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
$blocks = array_values(array_filter(array_map('trim', preg_split('/(?=<h2>|<blockquote>)/', $bodyHtml)), 'strlen'));

$trust = $isExternal
    ? ['Acesso imediato', 'Material Global Invest Brasil', 'Garantia de 7 dias']
    : ['Feito com o seu caso', 'Sem fórmula pronta', 'Método Global Invest Brasil'];
$alert = $isExternal
    ? 'Compra segura na loja oficial &middot; acesso imediato por e-mail.'
    : 'Conteúdo e método do Professor Jorge Dadalt, com décadas conduzindo negócios reais.';

// Livros e e-books ganham a capa em destaque; serviços usam foto de fundo.
$slugLower = strtolower((string) $p['slug']);
$isBook = in_array($category, ['Livros', 'E-books'], true) || (bool) preg_match('/livro|e-?book|patrimonio|ideia-ao-lucro/', $slugLower);
$isPoster = $image === '' || (bool) preg_match('#mentoria|palestra|curso#', $image);
$heroPhoto = $isPoster ? '/assets/images/publicacoes/gestao-editorial.webp' : $image;
$parent = match (true) {
    $isBook => ['Livros e e-books', '/conteudos.html#livros'],
    $category === 'Mentorias', $category === 'Cursos e palestras' => ['Mentorias', '/mentorias.html'],
    default => ['Soluções', '/solucoes.html'],
};
$shopHandle = preg_match('#^https://brcrystals\.com/products/([\w-]+)#', $buyUrl, $hm) ? $hm[1] : '';
$checkoutAttr = $shopHandle !== '' ? ' data-checkout="' . h($shopHandle) . '"' . ($price ? ' data-value="' . h((string) (float) $price) . '"' : '') : '';
$arrow = '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>';
$check = '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 4.5 4.5L19 7"/></svg>';
function pd_buy(string $url, string $attrs, string $label, string $extra, string $cls = 'btn'): string {
    global $arrow;
    return '<a class="' . $cls . '" href="' . h($url) . '"' . $attrs . $extra . '>' . h($label) . ' ' . $arrow . '</a>';
}
?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title><?= h($title) ?> | Global Invest Brasil</title>
<meta name="description" content="<?= h($summary) ?>">
<meta name="robots" content="index,follow,max-image-preview:large">
<meta name="theme-color" content="#0b1a1c">
<link rel="canonical" href="<?= h($canonical) ?>">
<meta property="og:type" content="product">
<meta property="og:locale" content="pt_BR">
<meta property="og:site_name" content="Global Invest Brasil">
<meta property="og:title" content="<?= h($title) ?>">
<meta property="og:description" content="<?= h($summary) ?>">
<meta property="og:url" content="<?= h($canonical) ?>">
<meta property="og:image" content="<?= h($ogImage) ?>">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Newsreader:ital,opsz,wght@0,6..72,300;0,6..72,400;0,6..72,500;1,6..72,300&display=swap">
<link rel="stylesheet" href="/assets/css/site.css?v=<?= SITE_ASSET_VERSION ?>">
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
<script>document.documentElement.classList.add("js")</script>
<script src="/assets/js/site.js?v=<?= SITE_ASSET_VERSION ?>" defer></script>
<script src="/assets/js/venda.js?v=<?= SITE_ASSET_VERSION ?>" defer></script>
<script src="/adsense-loader.php" defer></script>
</head>
<body>
<a class="skip-link" href="#conteudo">Ir para o conteúdo</a>
<?= site_header() ?>
<main id="conteudo">

<section class="sale-hero<?= $isBook ? '' : ' has-photo' ?>">
<?php if (!$isBook): ?><div class="page-hero-media"><img src="<?= h($heroPhoto) ?>" alt="" fetchpriority="high" data-parallax="0.08"></div><?php endif; ?>
<div class="wrap <?= $isBook ? 'sale-grid' : '' ?>">
<div class="<?= $isBook ? '' : 'page-hero-copy' ?>">
<nav class="crumbs fade-up" aria-label="Você está em"><a href="/">Início</a><span aria-hidden="true">/</span><a href="<?= h($parent[1]) ?>"><?= h($parent[0]) ?></a><span aria-hidden="true">/</span><span><?= h($category ?: 'Produto') ?></span></nav>
<p class="eyebrow fade-up" style="--d:.1s"><?= h($category ?: 'Global Invest Brasil') ?></p>
<h1 class="display d-lg fade-up" style="--d:.15s"><?= h($title) ?></h1>
<?php if ($summary !== ''): ?><p class="lede fade-up" style="--d:.3s"><?= h($summary) ?></p><?php endif; ?>
<div class="sale-cta fade-up" style="--d:.45s">
<?php if ($priceLabel !== ''): ?><div class="price-tag"><strong><?= h($priceLabel) ?></strong></div><?php endif; ?>
<?= pd_buy($buyUrl, $ctaAttrs, $cta, $checkoutAttr) ?>
<p class="sale-note"><?= h($buyNote) ?></p>
</div>
<div class="trust-row fade-up" style="--d:.6s"><?php foreach ($trust as $t): ?><span><?= $check ?><?= h($t) ?></span><?php endforeach; ?></div>
</div>
<?php if ($isBook && $image !== ''): ?>
<div class="cover-stage fade-up" style="--d:.3s"><img src="<?= h($image) ?>" alt="<?= h($title) ?>" decoding="async" fetchpriority="high"></div>
<?php endif; ?>
</div>
</section>

<section class="pd-alert"><div class="wrap"><p><?= $alert ?></p></div></section>

<?php
$tone = 0;
foreach ($blocks as $block):
    if (strncmp($block, '<blockquote>', 12) === 0):
        if (preg_match('#^<blockquote>(.*?)</blockquote>(.*)$#s', $block, $mm)):
            ?><section class="pull<?= $tone % 2 ? ' is-dark' : '' ?>"><div class="wrap"><span>Global Invest Brasil</span><div class="pd-quote"><?= $mm[1] ?></div></div></section><?php
            $block = trim($mm[2]);
            if ($block === '') continue;
        endif;
    endif;
    $heading = '';
    if (preg_match('#^<h2>(.*?)</h2>(.*)$#s', $block, $bm)) { $heading = $bm[1]; $block = trim($bm[2]); }
    $cls = ['band', 'band is-paper-2', 'band is-ink'][$tone % 3];
    $tone++;
    ?><section class="<?= $cls ?>"><div class="wrap two-col">
<div data-reveal><p class="eyebrow"><?= h($category ?: 'Global Invest Brasil') ?></p><?php if ($heading !== ''): ?><h2 class="h2"><?= $heading ?></h2><?php endif; ?></div>
<div class="pd-body" data-reveal style="--d:.08s"><?= $block ?></div>
</div></section><?php
endforeach;
?>

<section class="band is-ink" id="oferta">
<div class="wrap">
<div class="offer-box<?= $isBook && $image !== '' ? '' : ' is-solo' ?>" data-reveal>
<?php if ($isBook && $image !== ''): ?><div class="ob-cover"><img src="<?= h($image) ?>" alt="<?= h($title) ?>" loading="lazy"></div><?php endif; ?>
<div class="ob-body">
<p class="ob-head"><?= $isExternal ? 'Acesso imediato &middot; Compra segura' : 'Atendimento Global Invest Brasil' ?></p>
<h3><?= h($title) ?></h3>
<?php if ($summary !== ''): ?><p style="color:var(--muted)"><?= h($summary) ?></p><?php endif; ?>
<?php if ($priceLabel !== ''): ?><div class="price-tag"><strong><?= h($priceLabel) ?></strong></div><?php endif; ?>
<div class="btn-row"><?= pd_buy($buyUrl, $ctaAttrs, $cta, $checkoutAttr) ?></div>
<?php if ($isExternal): ?><p class="seals"><span><?= $check ?>Pagamento seguro</span><span><?= $check ?>Acesso imediato por e-mail</span><span><?= $check ?>Garantia de 7 dias</span></p><?php endif; ?>
</div>
</div>
<?php if ($isExternal): ?>
<div class="guarantee" data-reveal>
<div class="seal"><b>7</b><small>dias</small></div>
<div><h3>O risco é todo nosso.</h3><p>Se em até 7 dias você concluir que não serve para a sua realidade, responda o e-mail da compra e devolvemos 100% do valor.</p></div>
</div>
<?php endif; ?>
</div>
</section>

<?php if ($faq !== ''): ?>
<section class="band">
<div class="wrap two-col">
<div data-reveal><p class="eyebrow">Dúvidas frequentes</p><h2 class="h2">Perguntas frequentes.</h2></div>
<div class="faq" data-reveal style="--d:.08s"><?= $faq ?></div>
</div>
</section>
<?php endif; ?>

<section class="cta-band">
<div class="bg"><img src="/assets/images/publicacoes/negocios-digitais-editorial.webp" alt="" loading="lazy" data-parallax="0.1"></div>
<div class="wrap" data-reveal>
<p class="eyebrow">Próximo passo</p>
<h2 class="h2"><?= h($title) ?></h2>
<p class="lede">Converse com a Global Invest Brasil e veja como esta solução se encaixa no seu momento.</p>
<?= pd_buy($buyUrl, $ctaAttrs, $cta, $checkoutAttr) ?>
</div>
</section>

</main>
<?= site_footer() ?>
<div class="buybar" data-buybar>
<div class="bb-info"><b><?= $priceLabel !== '' ? h($priceLabel) : h($title) ?></b><small><?= h($category ?: 'Global Invest Brasil') ?></small></div>
<?= pd_buy($buyUrl, $ctaAttrs, $cta, $checkoutAttr) ?>
</div>
</body>
</html>
