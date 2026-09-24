<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';

cors_allow(['https://www.jorgedadalt.com', 'https://jorgedadalt.com']);

if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_response(['error' => 'Método não permitido'], 405);

const JD_CATALOG_JSONB_COLUMNS = ['points', 'trust', 'story', 'outcomes', 'audience', 'journey'];
const JD_CATALOG_CATEGORIES = ['livros-e-ebooks', 'cursos-e-mentorias', 'negocios-digitais'];
const JD_CATALOG_TYPES = ['livro', 'ebook', 'curso', 'mentoria', 'negocio'];

function jd_catalog_decode(array $row): array {
    foreach (JD_CATALOG_JSONB_COLUMNS as $col) {
        $row[$col] = json_decode((string) ($row[$col] ?? '[]'), true) ?: [];
    }
    unset($row['status']);
    return $row;
}

try {
    $pdo = db();
    $slug = trim((string) ($_GET['slug'] ?? ''));

    if ($slug !== '') {
        $stmt = $pdo->prepare("SELECT * FROM jd_catalog_items WHERE slug = ? AND status = 'published'");
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        if (!$row) json_response(['ok' => false, 'error' => 'not_found'], 404);
        json_response(['ok' => true, 'item' => jd_catalog_decode($row)]);
    }

    $category = trim((string) ($_GET['category'] ?? ''));
    $type = trim((string) ($_GET['type'] ?? ''));

    $where = ["status = 'published'"];
    $params = [];
    if ($category !== '' && in_array($category, JD_CATALOG_CATEGORIES, true)) {
        $where[] = 'category = ?';
        $params[] = $category;
    }
    if ($type !== '' && in_array($type, JD_CATALOG_TYPES, true)) {
        $where[] = 'item_type = ?';
        $params[] = $type;
    }

    $sql = 'SELECT * FROM jd_catalog_items WHERE ' . implode(' AND ', $where) . ' ORDER BY category, sort_order, id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = array_map('jd_catalog_decode', $stmt->fetchAll());

    json_response(['ok' => true, 'items' => $items]);
} catch (Throwable $e) {
    json_response(['ok' => false, 'error' => 'Não foi possível carregar o catálogo.'], 500);
}
