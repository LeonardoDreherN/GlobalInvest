<?php
require_once __DIR__ . '/_layout.php';
$pdo = db();

$statusLabels = [
    'novo' => 'Novo', 'em_analise' => 'Em análise', 'contato_realizado' => 'Contato realizado',
    'orcamento_enviado' => 'Orçamento enviado', 'aguardando_cliente' => 'Aguardando cliente',
    'aprovado' => 'Aprovado', 'nao_aprovado' => 'Não aprovado', 'projeto_iniciado' => 'Projeto iniciado',
    'concluido' => 'Concluído',
];
$productLabels = ['site' => 'Site', 'ecommerce' => 'E-commerce', 'aplicativo' => 'Aplicativo'];

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM briefings WHERE id = ?');
$stmt->execute([$id]);
$b = $stmt->fetch();
if (!$b) { admin_header('Formulário não encontrado'); echo '<p class="error">Registro não encontrado.</p><p><a class="button light" href="/admin/briefings.php">Voltar</a></p>'; admin_footer(); exit; }

$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $status = (string) ($_POST['status'] ?? $b['status']);
    $notes = (string) ($_POST['internal_notes'] ?? '');
    if (isset($statusLabels[$status])) {
        $pdo->prepare('UPDATE briefings SET status = ?, internal_notes = ?, updated_at = NOW() WHERE id = ?')->execute([$status, $notes, $id]);
        $stmt->execute([$id]);
        $b = $stmt->fetch();
        $notice = 'Alterações salvas.';
    }
}

$schemaPath = __DIR__ . '/../assets/data/formulario-perguntas.json';
$schema = json_decode((string) file_get_contents($schemaPath), true) ?: [];
$answers = json_decode((string) $b['answers'], true) ?: [];

function briefing_format_value($value): string {
    if (is_array($value)) return implode(', ', $value);
    if ($value === '' || $value === null) return '—';
    return (string) $value;
}

admin_header('Formulário #' . h($b['reference_code']));
if ($notice): ?><p class="notice"><?=h($notice)?></p><?php endif; ?>

<div class="no-print" style="margin-bottom:16px"><a class="button light" href="/admin/briefings.php">← Voltar para a lista</a> <button type="button" class="button light" onclick="window.print()">Imprimir / salvar em PDF</button></div>

<section class="card">
  <div class="view-grid">
    <div><span class="muted">Referência</span><br><b><?=h($b['reference_code'])?></b></div>
    <div><span class="muted">Produto</span><br><b><?=h($productLabels[$b['product']] ?? $b['product'])?></b></div>
    <div><span class="muted">Recebido em</span><br><b><?=h(substr((string) $b['created_at'], 0, 16))?></b></div>
    <div><span class="muted">Atualizado em</span><br><b><?=h(substr((string) $b['updated_at'], 0, 16))?></b></div>
  </div>
</section>

<section class="card no-print">
  <form method="post" class="grid">
    <input type="hidden" name="csrf" value="<?=csrf()?>">
    <label>Status<select name="status">
      <?php foreach ($statusLabels as $key => $label): ?><option value="<?=$key?>" <?=$b['status']===$key?'selected':''?>><?=h($label)?></option><?php endforeach; ?>
    </select></label>
    <label class="full">Notas internas<textarea name="internal_notes" placeholder="Observações da equipe, próximos passos, valor orçado..."><?=h($b['internal_notes'] ?? '')?></textarea></label>
    <div class="full actions"><button>Salvar</button></div>
  </form>
</section>

<section class="card">
  <h2 style="margin-top:0">Dados do cliente</h2>
  <dl class="answer-dl">
    <?php
    $clientValues = [
        'full_name' => $b['full_name'], 'company' => $b['company'], 'role_title' => $b['role_title'],
        'document_number' => $b['document_number'], 'email' => $b['email'], 'whatsapp' => $b['whatsapp'],
        'city' => $b['city'], 'state' => $b['state'], 'country' => $b['country'],
        'current_site' => $b['current_site'], 'instagram' => $b['instagram'], 'linkedin' => $b['linkedin'],
        'other_social' => $b['other_social'], 'how_found' => $b['how_found'],
    ];
    foreach ($schema['client_step']['fields'] ?? [] as $f):
        $val = $clientValues[$f['key']] ?? null;
        if ($val === null || $val === '') continue;
    ?>
    <dt><?=h($f['label'])?></dt><dd><?=h((string) $val)?></dd>
    <?php endforeach; ?>
  </dl>
</section>

<?php
$productSchema = $schema['products'][$b['product']] ?? null;
if ($productSchema):
    foreach ($productSchema['steps'] as $step):
        $items = [];
        foreach ($step['fields'] as $f) {
            if (!array_key_exists($f['key'], $answers)) continue;
            $v = $answers[$f['key']];
            if ($v === '' || $v === null || (is_array($v) && !$v)) continue;
            $items[] = $f;
        }
        if (!$items) continue;
?>
<section class="card">
  <h2 style="margin-top:0"><?=h($step['title'])?></h2>
  <dl class="answer-dl">
    <?php foreach ($items as $f): ?>
    <dt><?=h($f['label'])?></dt><dd><?=h(briefing_format_value($answers[$f['key']]))?></dd>
    <?php endforeach; ?>
  </dl>
</section>
<?php endforeach; endif; ?>

<style>
.view-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.answer-dl{margin:0;display:grid;grid-template-columns:minmax(200px,340px) 1fr;gap:10px 18px}
.answer-dl dt{font-weight:700;color:#526466;font-size:13px}
.answer-dl dd{margin:0}
@media(max-width:800px){.view-grid{grid-template-columns:1fr 1fr}.answer-dl{grid-template-columns:1fr}}
@media print{.no-print{display:none!important}.side{display:none!important}.layout{display:block!important}.main{padding:0!important}}
</style>

<?php admin_footer(); ?>
