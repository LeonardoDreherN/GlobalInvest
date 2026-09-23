<?php
require_once __DIR__ . '/_layout.php';
$pdo = db();
$notice = '';
$error = '';

$statusLabels = [
    'novo' => 'Novo', 'em_analise' => 'Em análise', 'contato_realizado' => 'Contato realizado',
    'orcamento_enviado' => 'Orçamento enviado', 'aguardando_cliente' => 'Aguardando cliente',
    'aprovado' => 'Aprovado', 'nao_aprovado' => 'Não aprovado', 'projeto_iniciado' => 'Projeto iniciado',
    'concluido' => 'Concluído',
];
$productLabels = ['site' => 'Site', 'ecommerce' => 'E-commerce', 'aplicativo' => 'Aplicativo'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    $status = (string) ($_POST['status'] ?? '');
    if ($id && isset($statusLabels[$status])) {
        $pdo->prepare('UPDATE briefings SET status = ?, updated_at = NOW() WHERE id = ?')->execute([$status, $id]);
        $notice = 'Status atualizado.';
    } else {
        $error = 'Não foi possível atualizar o status.';
    }
}

$q = trim((string) ($_GET['q'] ?? ''));
$product = (string) ($_GET['product'] ?? '');
$status = (string) ($_GET['status'] ?? '');
$dateFrom = (string) ($_GET['date_from'] ?? '');
$dateTo = (string) ($_GET['date_to'] ?? '');

$where = [];
$params = [];
if ($q !== '') {
    $where[] = '(full_name ILIKE :q OR company ILIKE :q OR email ILIKE :q OR whatsapp ILIKE :q OR reference_code ILIKE :q)';
    $params[':q'] = '%' . $q . '%';
}
if ($product !== '' && isset($productLabels[$product])) { $where[] = 'product = :product'; $params[':product'] = $product; }
if ($status !== '' && isset($statusLabels[$status])) { $where[] = 'status = :status'; $params[':status'] = $status; }
if ($dateFrom !== '') { $where[] = 'created_at >= :date_from'; $params[':date_from'] = $dateFrom . ' 00:00:00'; }
if ($dateTo !== '') { $where[] = 'created_at <= :date_to'; $params[':date_to'] = $dateTo . ' 23:59:59'; }

$sql = 'SELECT * FROM briefings';
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY created_at DESC LIMIT 300';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$counts = $pdo->query('SELECT status, COUNT(*) c FROM briefings GROUP BY status')->fetchAll();
$countByStatus = [];
foreach ($counts as $c) $countByStatus[$c['status']] = (int) $c['c'];
$total = array_sum($countByStatus);

admin_header('Formulários de projeto');
if ($notice): ?><p class="notice"><?=h($notice)?></p><?php endif;
if ($error): ?><p class="error"><?=h($error)?></p><?php endif; ?>

<section class="card">
  <h2 style="margin-top:0">Links para enviar ao cliente</h2>
  <p class="muted">Copie o link do produto certo (ou o link geral, para o cliente escolher) e envie por WhatsApp ou e-mail.</p>
  <div class="link-rows">
    <div class="link-row">
      <span class="link-tag">Geral</span>
      <input type="text" readonly data-link-path="/formulario/">
      <button type="button" class="button light" data-copy-btn>Copiar link</button>
      <button type="button" class="button light" data-whatsapp-btn>Enviar por WhatsApp</button>
    </div>
    <?php foreach ($productLabels as $key => $label): ?>
    <div class="link-row">
      <span class="link-tag"><?=h($label)?></span>
      <input type="text" readonly data-link-path="/formulario/?produto=<?=h($key)?>">
      <button type="button" class="button light" data-copy-btn>Copiar link</button>
      <button type="button" class="button light" data-whatsapp-btn>Enviar por WhatsApp</button>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="stats">
  <div class="stat"><b><?=$total?></b>Total</div>
  <div class="stat"><b><?=$countByStatus['novo'] ?? 0?></b>Novos</div>
  <div class="stat"><b><?=$countByStatus['em_analise'] ?? 0?></b>Em análise</div>
  <div class="stat"><b><?=$countByStatus['aprovado'] ?? 0?></b>Aprovados</div>
</section>

<section class="card">
  <form class="grid filter-form" method="get">
    <label>Buscar<input type="text" name="q" value="<?=h($q)?>" placeholder="Nome, empresa, e-mail, telefone ou referência"></label>
    <label>Produto<select name="product">
      <option value="">Todos</option>
      <?php foreach ($productLabels as $key => $label): ?><option value="<?=$key?>" <?=$product===$key?'selected':''?>><?=h($label)?></option><?php endforeach; ?>
    </select></label>
    <label>Status<select name="status">
      <option value="">Todos</option>
      <?php foreach ($statusLabels as $key => $label): ?><option value="<?=$key?>" <?=$status===$key?'selected':''?>><?=h($label)?></option><?php endforeach; ?>
    </select></label>
    <label>De<input type="date" name="date_from" value="<?=h($dateFrom)?>"></label>
    <label>Até<input type="date" name="date_to" value="<?=h($dateTo)?>"></label>
    <div class="full actions"><button>Filtrar</button><a class="button light" href="/admin/briefings.php">Limpar</a></div>
  </form>
</section>

<section class="card table-wrap">
  <table>
    <thead><tr><th>Referência</th><th>Data</th><th>Nome</th><th>Empresa</th><th>Produto</th><th>Telefone</th><th>E-mail</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
      <tr>
        <td><b><?=h($r['reference_code'])?></b></td>
        <td><?=h(substr((string) $r['created_at'], 0, 16))?></td>
        <td><?=h($r['full_name'])?></td>
        <td><?=h($r['company'] ?: '—')?></td>
        <td><?=h($productLabels[$r['product']] ?? $r['product'])?></td>
        <td><?=h($r['whatsapp'])?></td>
        <td><?=h($r['email'])?></td>
        <td>
          <form method="post" class="status-form">
            <input type="hidden" name="csrf" value="<?=csrf()?>">
            <input type="hidden" name="id" value="<?=$r['id']?>">
            <select name="status" onchange="this.form.submit()">
              <?php foreach ($statusLabels as $key => $label): ?><option value="<?=$key?>" <?=$r['status']===$key?'selected':''?>><?=h($label)?></option><?php endforeach; ?>
            </select>
          </form>
        </td>
        <td class="actions"><a class="button light" href="/admin/briefing-view.php?id=<?=$r['id']?>">Ver</a></td>
      </tr>
      <?php endforeach; if (!$rows): ?>
      <tr><td colspan="9">Nenhuma solicitação encontrada com estes filtros.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</section>

<style>
.link-rows{display:flex;flex-direction:column;gap:10px}
.link-row{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.link-tag{min-width:90px;font-weight:800;font-size:13px;color:#526466}
.link-row input{flex:1;min-width:220px;padding:9px 11px;border:1px solid #c7d6d2;border-radius:8px;font:inherit;background:#f7faf9}
.filter-form{grid-template-columns:repeat(5,minmax(0,1fr))}
.status-form select{margin-top:0;min-width:170px}
@media(max-width:900px){.filter-form{grid-template-columns:1fr 1fr}}
</style>
<script>
(function(){
  document.querySelectorAll('[data-copy-btn]').forEach(function(btn){
    btn.addEventListener('click', function(){
      var input = btn.closest('.link-row').querySelector('input[data-link-path]');
      var url = location.origin + input.getAttribute('data-link-path');
      input.value = url;
      navigator.clipboard && navigator.clipboard.writeText(url).then(function(){
        var old = btn.textContent; btn.textContent = 'Copiado!'; setTimeout(function(){ btn.textContent = old; }, 1500);
      });
    });
  });
  document.querySelectorAll('[data-whatsapp-btn]').forEach(function(btn){
    btn.addEventListener('click', function(){
      var input = btn.closest('.link-row').querySelector('input[data-link-path]');
      var url = location.origin + input.getAttribute('data-link-path');
      var msg = 'Olá! Para entendermos melhor o seu projeto e prepararmos um escopo adequado, pedimos que preencha o formulário da Global Invest Brasil:\n\n' + url + '\n\nApós o preenchimento, nossa equipe analisará as informações.';
      window.open('https://wa.me/?text=' + encodeURIComponent(msg), '_blank', 'noopener');
    });
  });
  document.querySelectorAll('input[data-link-path]').forEach(function(input){
    input.value = location.origin + input.getAttribute('data-link-path');
  });
})();
</script>

<?php admin_footer(); ?>
