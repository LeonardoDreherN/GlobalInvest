<?php
declare(strict_types=1);
// Lotes da Ecovila Mundo Verde para o site ecovilamundoverde.com.br (somente leitura).
require_once __DIR__ . '/../app/ecovila.php';

cors_allow(ECO_ORIGINS);
if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_response(['error' => 'Método não permitido'], 405);

try {
    eco_ensure_schema();
    $rows = db()->query('SELECT * FROM eco_lots WHERE is_visible = 1 ORDER BY number')->fetchAll();
    $lots = array_map(function (array $r): array {
        $lot = [
            'id' => (int) $r['number'],
            'title' => $r['title'],
            'status' => $r['status'],
            'statusLabel' => array_key_exists($r['status'], ECO_STATUSES) ? ECO_STATUSES[$r['status']] : $r['status'],
            'image' => $r['image_url'],
            'text' => $r['description'],
        ];
        // Mesmo formato que o script.js do site já usa: { valor, pendente }.
        foreach (array_keys(ECO_SPEC_FIELDS) as $f) $lot[$f] = ['valor' => $r[$f], 'pendente' => (bool) (int) $r[$f . '_pending']];
        return $lot;
    }, $rows);
    json_response(['ok' => true, 'lots' => $lots]);
} catch (Throwable $e) {
    // Tabela ainda não criada (o admin cria no primeiro acesso) ou banco indisponível:
    // o site usa a lista que já tem embutida.
    json_response(['ok' => false, 'error' => 'Lotes indisponíveis no momento.'], 503);
}
