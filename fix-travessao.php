<?php
declare(strict_types=1);
/*
 * Uso ÚNICO. Remove travessões (— e –) do conteúdo já gravado no banco
 * (produtos, publicações, blog): "** — X" vira "**: X" e os demais viram
 * vírgula. Exige login no /admin/. Depois de conferir, APAGUE este arquivo.
 */
require_once __DIR__ . '/app/auth.php';
require_admin();
header('Content-Type: text/plain; charset=utf-8');

$pdo = db();
$map = [
    'products'     => ['title', 'summary', 'body', 'cta_label'],
    'publications' => ['title', 'excerpt', 'content', 'seo_title', 'seo_description'],
    'blog_posts'   => ['title', 'excerpt', 'content', 'seo_title', 'seo_description'],
];

$total = 0;
foreach ($map as $table => $fields) {
    foreach ($fields as $f) {
        $sql = "UPDATE {$table} SET {$f} = regexp_replace(
                    regexp_replace(
                        regexp_replace({$f}, '\\*\\*\\s*[\u{2014}\u{2013}]\\s*', '**: ', 'g'),
                    '\\s*[\u{2014}\u{2013}]\\s*', ', ', 'g'),
                ', ,', ',', 'g')
                WHERE {$f} ~ '[\u{2014}\u{2013}]'";
        try {
            $n = $pdo->exec($sql);
            if ($n) { $total += $n; echo "OK    {$table}.{$f}  ({$n} registro" . ($n > 1 ? 's' : '') . ")\n"; }
        } catch (Throwable $e) {
            echo "ERRO  {$table}.{$f}: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n--------\nCampos atualizados: {$total}\n";
echo "Confira o site. Depois APAGUE este arquivo (fix-travessao.php).\n";
