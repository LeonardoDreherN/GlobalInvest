<?php
require_once __DIR__ . '/_layout.php';
$pdo = db();
$notice = '';
$error = '';

$categoryLabels = ['livros-e-ebooks' => 'Livros e E-books', 'cursos-e-mentorias' => 'Cursos e Mentorias', 'negocios-digitais' => 'Negócios Digitais'];
$typeLabels = ['livro' => 'Livro', 'ebook' => 'E-book', 'curso' => 'Curso', 'mentoria' => 'Mentoria', 'negocio' => 'Negócio'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    $formAction = (string) ($_POST['form_action'] ?? '');
    if ($formAction === 'delete' && $id) {
        $pdo->prepare('DELETE FROM jd_catalog_items WHERE id = ?')->execute([$id]);
        $notice = 'Item excluído.';
    }
}

$q = trim((string) ($_GET['q'] ?? ''));
$category = (string) ($_GET['category'] ?? '');
$type = (string) ($_GET['type'] ?? '');
$status = (string) ($_GET['status'] ?? '');

$where = [];
$params = [];
if ($q !== '') { $where[] = '(title ILIKE :q OR slug ILIKE :q)'; $params[':q'] = '%' . $q . '%'; }
if ($category !== '' && isset($categoryLabels[$category])) { $where[] = 'category = :category'; $params[':category'] = $category; }
if ($type !== '' && isset($typeLabels[$type])) { $where[] = 'item_type = :type'; $params[':type'] = $type; }
if ($status !== '' && in_array($status, ['draft', 'published'], true)) { $where[] = 'status = :status'; $params[':status'] = $status; }

$sql = 'SELECT * FROM jd_catalog_items';
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY category, sort_order, id';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

admin_header('Catálogo Jorge Dadalt');
if ($notice): ?><p class="notice"><?=h($notice)?></p><?php endif;
if ($error): ?><p class="error"><?=h($error)?></p><?php endif; ?>

<p class="muted">Livros, e-books, cursos, mentorias e soluções digitais exibidos em
<a href="https://www.jorgedadalt.com" target="_blank" rel="noopener">jorgedadalt.com</a>.
Editar aqui atualiza o site automaticamente (sem precisar reenviar arquivos).</p>

<div class="actions" style="margin-bottom:6px">
  <a class="button orange" href="/admin/jd-catalog-edit.php?action=new"><?=admin_icon('plus-circle')?> Cadastrar item</a>
  <span class="badge" style="align-self:center"><?=count($rows)?> <?=count($rows)===1?'item':'itens'?> no total</span>
</div>

<section class="card">
  <form class="grid filter-form" method="get">
    <label>Buscar<input type="text" name="q" value="<?=h($q)?>" placeholder="Título ou slug"></label>
    <label>Categoria<select name="category">
      <option value="">Todas</option>
      <?php foreach ($categoryLabels as $key => $label): ?><option value="<?=$key?>" <?=$category===$key?'selected':''?>><?=h($label)?></option><?php endforeach; ?>
    </select></label>
    <label>Tipo<select name="type">
      <option value="">Todos</option>
      <?php foreach ($typeLabels as $key => $label): ?><option value="<?=$key?>" <?=$type===$key?'selected':''?>><?=h($label)?></option><?php endforeach; ?>
    </select></label>
    <label>Status<select name="status">
      <option value="">Todos</option>
      <option value="published" <?=$status==='published'?'selected':''?>>Publicado</option>
      <option value="draft" <?=$status==='draft'?'selected':''?>>Rascunho</option>
    </select></label>
    <div class="full actions"><button>Filtrar</button><a class="button light" href="/admin/jd-catalog.php">Limpar</a></div>
  </form>
</section>

<section class="card table-wrap">
  <table>
    <thead><tr><th></th><th>Título</th><th>Categoria / Tipo</th><th>Status</th><th>Atualizado</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): $liveUrl = 'https://www.jorgedadalt.com/' . $r['category'] . '/' . $r['url_slug'] . '/'; ?>
      <tr>
        <td><?php if ($r['image_url']): $img = str_starts_with((string) $r['image_url'], 'http') ? $r['image_url'] : 'https://www.jorgedadalt.com' . $r['image_url']; ?><img class="row-thumb" src="<?=h($img)?>" alt=""><?php endif; ?></td>
        <td><b><?=h($r['title'])?></b><br><small class="muted"><?=h($r['slug'])?></small></td>
        <td><?=h($categoryLabels[$r['category']] ?? $r['category'])?><br><small class="muted"><?=h($typeLabels[$r['item_type']] ?? $r['item_type'])?></small></td>
        <td><span class="badge" data-status="<?=h($r['status'])?>"><?=$r['status']==='published'?'Publicado':'Rascunho'?></span></td>
        <td><?=h(substr((string) $r['updated_at'], 0, 16))?></td>
        <td class="actions">
          <a class="button light" target="_blank" rel="noopener" href="<?=h($liveUrl)?>">Ver ↗</a>
          <a class="button light" href="/admin/jd-catalog-edit.php?action=edit&id=<?=$r['id']?>">Editar</a>
          <form method="post" onsubmit="return confirm('Excluir este item do catálogo?')">
            <input type="hidden" name="csrf" value="<?=csrf()?>">
            <input type="hidden" name="form_action" value="delete">
            <input type="hidden" name="id" value="<?=$r['id']?>">
            <button class="button danger">Excluir</button>
          </form>
        </td>
      </tr>
      <?php endforeach; if (!$rows): ?>
      <tr><td colspan="6">Nenhum item encontrado com estes filtros.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</section>

<style>.filter-form{grid-template-columns:repeat(4,minmax(0,1fr))}@media(max-width:900px){.filter-form{grid-template-columns:1fr 1fr}}</style>

<?php admin_footer(); ?>
