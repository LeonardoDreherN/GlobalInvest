<?php
declare(strict_types=1);
/*
 * Uso ÚNICO. Reescreve o corpo (body) dos 5 produtos que tinham HTML próprio
 * — a migração automática embolou as listas "Como funciona". Exige login no
 * /admin/. Depois de conferir, APAGUE este arquivo.
 *
 * ATENÇÃO: sobrescreve o campo "Descrição completa" desses 5 produtos. Se você
 * já editou algum deles à mão no painel, revise depois de rodar.
 */
require_once __DIR__ . '/app/auth.php';
require_admin();
header('Content-Type: text/plain; charset=utf-8');

$json = __DIR__ . '/database/produtos-corpo.json';
if (!is_file($json)) { echo "database/produtos-corpo.json não encontrado.\n"; exit; }
$bodies = json_decode((string) file_get_contents($json), true) ?: [];

$pdo = db();
$stmt = $pdo->prepare("UPDATE products SET body = :body, updated_at = NOW() WHERE slug = :slug");
$done = 0;
foreach ($bodies as $slug => $body) {
    try {
        $stmt->execute([':body' => $body, ':slug' => $slug]);
        $n = $stmt->rowCount();
        $done += $n;
        echo ($n > 0 ? "OK    " : "SEM MUDANÇA  ") . $slug . "\n";
    } catch (Throwable $e) {
        echo "ERRO  {$slug}: " . $e->getMessage() . "\n";
    }
}
echo "\n--------\nAtualizados: {$done}\n";
echo "Confira em /produto/produto-site , /produto/produto-livro etc. Depois APAGUE este arquivo.\n";
