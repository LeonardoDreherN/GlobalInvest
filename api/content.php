<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';
try {
    $pdo = db();
    $products = $pdo->query("SELECT p.id,p.title,p.slug,p.summary,p.image_url AS image,p.purchase_url AS link,p.cta_label AS linkLabel,c.name AS category FROM products p LEFT JOIN product_categories c ON c.id=p.category_id WHERE p.status='published' ORDER BY p.featured DESC,p.published_at DESC")->fetchAll();
    $publications = $pdo->query("SELECT p.id,p.title,p.slug,p.excerpt AS summary,p.image_url AS image,p.image_alt,p.published_at,c.name AS category,CONCAT('/publicacao/',p.slug) AS link,'Saiba mais' AS linkLabel FROM publications p LEFT JOIN publication_categories c ON c.id=p.category_id WHERE p.status='published' ORDER BY p.published_at DESC")->fetchAll();
    $blog = $pdo->query("SELECT p.id,p.title,p.slug,p.excerpt AS summary,p.image_url AS image,p.image_alt,p.published_at,c.name AS category,CONCAT('/blog/',p.slug) AS link,'Ler artigo' AS linkLabel FROM blog_posts p LEFT JOIN blog_categories c ON c.id=p.category_id WHERE p.status='published' ORDER BY p.published_at DESC")->fetchAll();
    json_response(['home' => [], 'pages' => ['produtos' => $products, 'publicacoes' => $publications, 'blog' => $blog]]);
} catch (Throwable $e) { json_response(['home' => [], 'pages' => ['produtos'=>[], 'publicacoes'=>[], 'blog'=>[]]]); }
