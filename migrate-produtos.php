<?php
declare(strict_types=1);
/*
 * Uso ÚNICO. Migra os 12 produtos (5 HTML em /produto/*.html + 7 do
 * product-pages.js via database/produtos-extra.json) para a tabela `products`.
 * Exige login no /admin/. Idempotente (ON CONFLICT (slug) DO NOTHING).
 * Depois de conferir em /produtos.html, APAGUE este arquivo.
 */
require_once __DIR__ . '/app/auth.php';
require_admin();
header('Content-Type: text/plain; charset=utf-8');

function txt(?DOMNode $n): string { return $n ? trim(preg_replace('/\s+/u', ' ', $n->textContent)) : ''; }
function q1(DOMXPath $xp, string $query, ?DOMNode $ctx = null): ?DOMNode {
    $l = $ctx ? $xp->query($query, $ctx) : $xp->query($query);
    return ($l && $l->length) ? $l->item(0) : null;
}
function normUrl(string $u): string {
    $u = trim($u);
    if ($u === '') return '';
    if (preg_match('#^https?://#i', $u)) return $u;
    return preg_replace('#^(\.\./)+#', '/', $u);
}

$pdo = db();
$catMap = [];
foreach ($pdo->query('SELECT id, lower(name) AS n FROM product_categories') as $r) $catMap[$r['n']] = (int) $r['id'];

// slug -> categoria para os 5 HTML
$catHtml = [
    'produto-livro' => 'Livros',
    'produto-ideia-ao-lucro' => 'E-books',
    'produto-site' => 'Sites e e-commerces',
    'produto-ecommerce' => 'Sites e e-commerces',
    'produto-app-dedicado' => 'Sites e e-commerces',
];

$insert = $pdo->prepare(
    "INSERT INTO products (category_id, title, slug, summary, body, image_url, purchase_url, cta_label, price, status, featured, published_at)
     VALUES (:cat, :title, :slug, :summary, :body, :img, :buy, :cta, :price, 'published', :featured, CURRENT_TIMESTAMP)
     ON CONFLICT (slug) DO NOTHING"
);

$done = 0; $skipped = 0; $errors = [];
function salva(PDOStatement $insert, array $row, ?int $catId, int &$done, int &$skipped, array &$errors): void {
    try {
        $insert->execute([
            ':cat' => $catId,
            ':title' => $row['title'] ?: $row['slug'],
            ':slug' => $row['slug'],
            ':summary' => $row['summary'] ?? '',
            ':body' => $row['body'] ?? '',
            ':img' => ($row['image_url'] ?? '') ?: null,
            ':buy' => ($row['purchase_url'] ?? '') ?: null,
            ':cta' => ($row['cta_label'] ?? '') ?: 'Quero saber mais',
            ':price' => ($row['price'] ?? null) ?: null,
            ':featured' => !empty($row['featured']) ? 1 : 0,
        ]);
        if ($insert->rowCount() > 0) { $done++; echo "OK    {$row['slug']}\n"; }
        else { $skipped++; echo "JÁ EXISTE  {$row['slug']}\n"; }
    } catch (Throwable $e) {
        $errors[] = "{$row['slug']}: " . $e->getMessage();
        echo "ERRO  {$row['slug']}: " . $e->getMessage() . "\n";
    }
}

// ---- 5 páginas HTML próprias -------------------------------------------------
foreach (glob(__DIR__ . '/produto/*.html') ?: [] as $file) {
    $slug = basename($file, '.html');
    if (!isset($catHtml[$slug])) continue; // as demais são do product-pages.js
    try {
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8" ?>' . file_get_contents($file));
        libxml_clear_errors();
        $xp = new DOMXPath($doc);

        $title = txt(q1($xp, '//h1'));
        $summary = txt(q1($xp, "//*[contains(@class,'ebook-lead')]"));

        $img = '';
        $og = q1($xp, '//meta[@property="og:image"]');
        if ($og) $img = trim($og->getAttribute('content'));
        if ($img === '') { $hero = q1($xp, "//figure//img | //*[contains(@class,'ebook-hero-media')]//img"); if ($hero) $img = $hero->getAttribute('src'); }
        $img = normUrl($img);

        $ctaA = q1($xp, "//a[contains(@class,'ebook-primary-cta')]");
        $buy = $ctaA ? normUrl($ctaA->getAttribute('href')) : '';
        $cta = $ctaA ? trim(preg_replace('/\s*→?\s*$/u', '', txt($ctaA))) : '';

        // corpo em Markdown a partir das seções de conteúdo
        $md = [];
        foreach ($xp->query("//*[contains(@class,'ebook-editorial-copy')]//p") as $p) $md[] = txt($p);
        $cards = $xp->query("//*[contains(@class,'ebook-card-grid')]/article");
        if ($cards->length) {
            $md[] = '## O que está incluído';
            foreach ($cards as $c) {
                $h = txt(q1($xp, './/h3', $c)); $d = txt(q1($xp, './/p', $c));
                if ($h) $md[] = "- **{$h}** — {$d}";
            }
        }
        $steps = $xp->query("//*[contains(@class,'ebook-step-list')]/li");
        if ($steps->length) {
            $md[] = '## Como funciona';
            $i = 1;
            foreach ($steps as $s) { $md[] = $i++ . '. ' . txt($s); }
        }
        foreach ($xp->query('//details') as $d) {
            $sum = txt(q1($xp, './/summary', $d));
            $ans = txt(q1($xp, './/p', $d));
            if ($sum) { $md[] = "## {$sum}"; $md[] = $ans; }
        }

        salva($insert, [
            'slug' => $slug, 'title' => $title, 'summary' => $summary,
            'body' => implode("\n\n", array_filter($md)),
            'image_url' => $img, 'purchase_url' => $buy, 'cta_label' => $cta,
            'featured' => in_array($slug, ['produto-livro', 'produto-ideia-ao-lucro'], true),
        ], $catMap[mb_strtolower($catHtml[$slug])] ?? null, $done, $skipped, $errors);
    } catch (Throwable $e) {
        $errors[] = "$slug: " . $e->getMessage();
        echo "ERRO  {$slug}: " . $e->getMessage() . "\n";
    }
}

// ---- 7 do product-pages.js (JSON) -----------------------------------------
$extraJson = __DIR__ . '/database/produtos-extra.json';
if (is_file($extraJson)) {
    foreach (json_decode((string) file_get_contents($extraJson), true) ?: [] as $row) {
        if (empty($row['slug'])) continue;
        salva($insert, $row, $catMap[mb_strtolower((string) ($row['category'] ?? ''))] ?? null, $done, $skipped, $errors);
    }
}

echo "\n--------\nInseridos: {$done}\nIgnorados: {$skipped}\n";
$total = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
echo "Total na tabela products: {$total}\n";
if ($errors) echo "\nAvisos:\n - " . implode("\n - ", $errors) . "\n";
echo "\nConfira em /produtos.html. Depois APAGUE este arquivo (migrate-produtos.php).\n";
