<?php
declare(strict_types=1);
require_once __DIR__ . '/app/bootstrap.php';
header('Content-Type: application/xml; charset=utf-8');
$urls = ['/','/index.html','/produtos.html','/mentorias.html','/publicacoes.html','/artigos.html','/seu-negocio.html','/contato.html','/privacidade.html','/politicas-de-uso.html'];

foreach (glob(__DIR__ . '/produto/*.html') ?: [] as $file) $urls[] = '/produto/' . rawurlencode(basename($file));
foreach (glob(__DIR__ . '/publicacao/*.html') ?: [] as $file) $urls[] = '/publicacao/' . rawurlencode(basename($file));

try { foreach (db()->query("SELECT slug FROM publications WHERE status='published'") as $r) $urls[] = '/publicacao/' . rawurlencode($r['slug']); } catch (Throwable $e) {}
try { foreach (db()->query("SELECT slug FROM blog_posts WHERE status='published'") as $r) $urls[] = '/blog/' . rawurlencode($r['slug']); } catch (Throwable $e) {}

$base = base_url(); echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">"; foreach(array_unique($urls) as $url) echo '<url><loc>' . h($base . $url) . '</loc><lastmod>' . gmdate('Y-m-d') . '</lastmod></url>'; echo '</urlset>';
