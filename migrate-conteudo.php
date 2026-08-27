<?php
declare(strict_types=1);
/*
 * Uso ÚNICO. Migra as publicações estáticas de /publicacao/*.html para a
 * tabela `publications`. Exige estar logado no /admin/. É idempotente
 * (ON CONFLICT (slug) DO NOTHING) — pode rodar de novo sem duplicar.
 * Depois de conferir em /publicacoes.html, APAGUE este arquivo.
 */
require_once __DIR__ . '/app/auth.php';
require_admin();

header('Content-Type: text/plain; charset=utf-8');

function inner_html(DOMNode $node): string {
    $html = '';
    foreach ($node->childNodes as $child) {
        $html .= $node->ownerDocument->saveHTML($child);
    }
    return trim($html);
}

function first_node(DOMXPath $xp, string $query, ?DOMNode $ctx = null): ?DOMNode {
    $list = $ctx ? $xp->query($query, $ctx) : $xp->query($query);
    return ($list && $list->length) ? $list->item(0) : null;
}

$pdo = db();
$catMap = [];
foreach ($pdo->query('SELECT id, lower(name) AS n FROM publication_categories') as $r) {
    $catMap[$r['n']] = (int) $r['id'];
}

$files = glob(__DIR__ . '/publicacao/*.html') ?: [];
sort($files);
$done = 0; $skipped = 0; $errors = [];

foreach ($files as $file) {
    $slug = basename($file, '.html');
    try {
        $raw = file_get_contents($file);
        if ($raw === false) throw new RuntimeException('não foi possível ler');

        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8" ?>' . $raw);
        libxml_clear_errors();
        $xp = new DOMXPath($doc);

        $bodyNode = first_node($xp, "//*[contains(concat(' ', normalize-space(@class), ' '), ' publication-body ')]");
        if (!$bodyNode) { $skipped++; $errors[] = "$slug: sem .publication-body"; continue; }

        $h1 = first_node($xp, '//h1');
        $title = $h1 ? trim($h1->textContent) : $slug;

        $catSpan = first_node($xp, "//*[contains(concat(' ', normalize-space(@class), ' '), ' publication-heading ')]/span");
        $catName = $catSpan ? trim($catSpan->textContent) : '';
        $categoryId = $catMap[mb_strtolower($catName)] ?? null;

        $deckNode = first_node($xp, "//*[contains(concat(' ', normalize-space(@class), ' '), ' publication-deck ')]");
        $excerpt = $deckNode ? trim($deckNode->textContent) : '';

        $timeNode = first_node($xp, '//time[@datetime]');
        $publishedAt = $timeNode ? substr(trim($timeNode->getAttribute('datetime')), 0, 10) . ' 12:00:00' : null;

        $titleTag = first_node($xp, '//title');
        $seoTitle = $titleTag ? trim(preg_replace('/\s*\|\s*Global Invest Brasil\s*$/u', '', $titleTag->textContent)) : '';

        $descTag = first_node($xp, '//meta[@name="description"]');
        $seoDescription = $descTag ? trim($descTag->getAttribute('content')) : '';

        // Imagem principal: a figura editorial que abre o corpo. Removida do corpo
        // para não repetir (o article.php a renderiza a partir de image_url).
        $imageUrl = ''; $imageAlt = '';
        $figNode = first_node($xp, ".//figure[contains(concat(' ', normalize-space(@class), ' '), ' publication-editorial-image ')]", $bodyNode);
        if ($figNode) {
            $img = first_node($xp, './/img', $figNode);
            if ($img) { $imageUrl = trim($img->getAttribute('src')); $imageAlt = trim($img->getAttribute('alt')); }
            $figNode->parentNode->removeChild($figNode);
        }
        if ($imageUrl === '') {
            $og = first_node($xp, '//meta[@property="og:image"]');
            if ($og) $imageUrl = trim($og->getAttribute('content'));
        }
        // src relativo (../assets/...) -> absoluto
        $imageUrl = preg_replace('#^(\.\./)+#', '/', $imageUrl);

        $content = inner_html($bodyNode);
        if ($content === '') { $skipped++; $errors[] = "$slug: corpo vazio"; continue; }

        $stmt = $pdo->prepare(
            "INSERT INTO publications (category_id, title, slug, excerpt, content, image_url, image_alt, author_name, seo_title, seo_description, status, published_at)
             VALUES (:cat, :title, :slug, :excerpt, :content, :img, :alt, 'Global Invest Brasil', :seotitle, :seodesc, 'published', :pub)
             ON CONFLICT (slug) DO NOTHING"
        );
        $stmt->execute([
            ':cat' => $categoryId,
            ':title' => $title,
            ':slug' => $slug,
            ':excerpt' => $excerpt,
            ':content' => $content,
            ':img' => $imageUrl ?: null,
            ':alt' => $imageAlt ?: null,
            ':seotitle' => $seoTitle ?: null,
            ':seodesc' => $seoDescription ?: null,
            ':pub' => $publishedAt,
        ]);
        if ($stmt->rowCount() > 0) {
            $done++;
            echo "OK    {$slug}  [{$catName}]  {$publishedAt}\n";
        } else {
            $skipped++;
            echo "JÁ EXISTE  {$slug}\n";
        }
    } catch (Throwable $e) {
        $errors[] = "$slug: " . $e->getMessage();
        echo "ERRO  {$slug}: " . $e->getMessage() . "\n";
    }
}

echo "\n--------\n";
echo "Inseridas: {$done}\nIgnoradas: {$skipped}\n";
$total = (int) $pdo->query('SELECT COUNT(*) FROM publications')->fetchColumn();
echo "Total na tabela publications: {$total}\n";
if ($errors) {
    echo "\nAvisos:\n - " . implode("\n - ", $errors) . "\n";
}
echo "\nConfira em /publicacoes.html. Depois APAGUE este arquivo (migrate-conteudo.php).\n";
