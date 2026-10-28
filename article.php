<?php
declare(strict_types=1);
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/markdown.php';
require_once __DIR__ . '/app/site-shell.php';

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
    ?><!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"><meta name="robots" content="noindex"><title>Conteúdo não encontrado | Global Invest Brasil</title><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Newsreader:opsz,wght@6..72,300;6..72,400&display=swap"><link rel="stylesheet" href="/assets/css/site.css?v=<?= SITE_ASSET_VERSION ?>"><script src="/assets/js/site.js?v=<?= SITE_ASSET_VERSION ?>" defer></script></head><body><?= site_header() ?><main id="conteudo"><section class="page-hero"><div class="wrap"><p class="eyebrow">Conteúdo não encontrado</p><h1 class="h2" style="margin:22px 0 18px">Esta publicação não existe, saiu do ar ou mudou de endereço.</h1><div class="btn-row"><a class="btn" href="/conteudos.html">Ver todos os conteúdos</a></div></div></section></main><?= site_footer() ?></body></html><?php
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
$canonical = base_url() . ($isBlog ? '/blog/' : '/publicacao/') . rawurlencode($p['slug']);
$ogImage = (string) ($p['image_url'] ?? '') ?: (base_url() . '/assets/images/globalinvest-social.jpg');
$bodyHtml = md_to_html($p['content'] ?? '');
?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title><?= h($pageTitle) ?> | Global Invest Brasil</title>
<meta name="description" content="<?= h($desc) ?>">
<meta name="robots" content="index,follow,max-image-preview:large">
<meta name="theme-color" content="#0b1a1c">
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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Newsreader:ital,opsz,wght@0,6..72,300;0,6..72,400;1,6..72,300&display=swap">
<link rel="stylesheet" href="/assets/css/site.css?v=<?= SITE_ASSET_VERSION ?>">
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
<script>document.documentElement.classList.add("js")</script>
<script src="/assets/js/site.js?v=<?= SITE_ASSET_VERSION ?>" defer></script>
<script src="/adsense-loader.php" defer></script>
</head>
<body>
<a class="skip-link" href="#conteudo">Ir para o conteúdo</a>
<?= site_header() ?>
<main id="conteudo">
<article>
<header class="page-hero">
<div class="wrap">
<nav class="crumbs" aria-label="Você está em"><a href="/">Início</a><span aria-hidden="true">/</span><a href="/conteudos.html">Conteúdos</a><?php if ($category !== ''): ?><span aria-hidden="true">/</span><span><?= h($category) ?></span><?php endif; ?></nav>
<p class="eyebrow"><?= h($isBlog ? 'Artigo' : 'Publicação') ?></p>
<h1 class="display d-lg" style="margin-top:24px;max-width:22ch"><?= h($title) ?></h1>
<?php if ($desc !== ''): ?><p class="lede" style="margin-top:24px"><?= h($desc) ?></p><?php endif; ?>
<p class="article-meta"><?php if ($dateLabel !== ''): ?><time datetime="<?= h($dateAttr) ?>"><?= h($dateLabel) ?></time><?php endif; ?><span><?= h((string) ($p['author_name'] ?? '') ?: 'Global Invest Brasil') ?></span></p>
</div>
</header>
<?php if (!empty($p['image_url'])): ?>
<div class="wrap"><figure class="article-cover"><img src="<?= h($p['image_url']) ?>" alt="<?= h(($p['image_alt'] ?? '') ?: $title) ?>"></figure></div>
<?php endif; ?>
<section class="band">
<div class="wrap legal-layout">
<nav class="toc" data-toc aria-label="Neste artigo"><h2>Neste artigo</h2></nav>
<div class="prose" data-toc-source><?= $bodyHtml ?></div>
</div>
</section>
</article>
<section class="cta-band">
<div class="bg"><img src="/assets/images/publicacoes/negocios-digitais-editorial.webp" alt="" loading="lazy" data-parallax="0.1"></div>
<div class="wrap">
<p class="eyebrow">Vamos conversar</p>
<h2 class="h2">Quer aplicar isso no seu negócio?</h2>
<p class="lede">Conte o seu momento e indicamos a solução que faz sentido agora.</p>
<a class="btn" href="/contato.html">Fale com a gente</a>
</div>
</section>
</main>
<?= site_footer() ?>
</body>
</html>
