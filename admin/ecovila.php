<?php
// Ecovila Mundo Verde: lista de lotes. A edição de cada lote fica em ecovila-lote.php.
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/../app/ecovila.php';
require_admin();

$pdo = db();
$notice = '';
$error = '';
try { eco_ensure_schema(); } catch (Throwable $e) { $error = 'Não foi possível preparar a tabela de lotes: ' . $e->getMessage(); }

// Troca rápida de status direto na lista (ex.: lote vendido).
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    verify_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    $status = (string) ($_POST['status'] ?? '');
    if ($id && array_key_exists($status, ECO_STATUSES)) {
        $pdo->prepare('UPDATE eco_lots SET status = ?, updated_at = NOW() WHERE id = ?')->execute([$status, $id]);
        header('Location: /admin/ecovila.php?status_saved=1');
        exit;
    }
}
if (isset($_GET['status_saved'])) $notice = 'Status do lote atualizado. O site já mostra a mudança.';
if (isset($_GET['deleted'])) $notice = 'Lote excluído.';

$rows = $error ? [] : $pdo->query('SELECT * FROM eco_lots ORDER BY number')->fetchAll();
$byStatus = array_fill_keys(array_keys(ECO_STATUSES), 0);
foreach ($rows as $r) $byStatus[$r['status']] = ($byStatus[$r['status']] ?? 0) + 1;
$contactsNew = 0;
try { $contactsNew = (int) $pdo->query("SELECT COUNT(*) FROM contacts WHERE site = 'ecovila' AND status = 'new'")->fetchColumn(); } catch (Throwable $e) {}

admin_header('Lotes da Ecovila');
if ($notice): ?><p class="notice"><?=h($notice)?></p><?php endif;
if ($error): ?><p class="error"><?=h($error)?></p><?php endif; ?>

<p class="muted">Os lotes exibidos em <a href="<?=h(ECO_SITE_URL)?>/lotes.html" target="_blank" rel="noopener">ecovila-mundo-verde.vercel.app/lotes</a>.
Editar aqui atualiza o site automaticamente, sem precisar reenviar arquivos.</p>

<div class="eco-summary">
  <?php foreach (ECO_STATUSES as $key => $label): ?>
    <div class="eco-stat eco-<?=h($key)?>"><span><?=h($label)?></span><b><?=$byStatus[$key]?></b></div>
  <?php endforeach; ?>
  <a class="eco-stat eco-contacts" href="/admin/manage.php?entity=contacts&site=ecovila"><span>Contatos novos</span><b><?=$contactsNew?></b></a>
</div>

<div class="actions" style="margin:6px 0">
  <a class="button orange" href="/admin/ecovila-lote.php?action=new"><?=admin_icon('plus-circle')?> Cadastrar lote</a>
</div>

<section class="card table-wrap">
  <table>
    <thead><tr><th></th><th>Lote</th><th>Área / Localização</th><th>Status</th><th>No site</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): $img = eco_image_preview((string) $r['image_url']); ?>
      <tr>
        <td><?php if ($img): ?><img class="row-thumb" src="<?=h($img)?>" alt=""><?php endif; ?></td>
        <td><b><?=h($r['title'])?></b><br><small class="muted">Nº <?=(int) $r['number']?> · <?=h($r['model'])?></small></td>
        <td><?=h($r['area'])?><?=$r['area_pending'] ? ' <small class="muted">(provisório)</small>' : ''?><br><small class="muted"><?=h($r['location'])?></small></td>
        <td>
          <form method="post" class="eco-status-form">
            <input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="id" value="<?=(int) $r['id']?>">
            <select name="status" aria-label="Status do <?=h($r['title'])?>" onchange="this.form.submit()">
              <?php foreach (ECO_STATUSES as $key => $label): ?><option value="<?=h($key)?>"<?=$r['status'] === $key ? ' selected' : ''?>><?=h($label)?></option><?php endforeach; ?>
            </select>
            <noscript><button class="button light">Salvar</button></noscript>
          </form>
        </td>
        <td><span class="badge" data-status="<?=$r['is_visible'] ? 'published' : 'draft'?>"><?=$r['is_visible'] ? 'Visível' : 'Oculto'?></span></td>
        <td class="actions"><a class="button light" href="/admin/ecovila-lote.php?action=edit&id=<?=(int) $r['id']?>">Editar</a></td>
      </tr>
    <?php endforeach; if (!$rows && !$error): ?>
      <tr><td colspan="6">Nenhum lote cadastrado.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</section>
<?php admin_footer();
