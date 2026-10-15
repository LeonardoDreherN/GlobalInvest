<?php
// Ecovila Mundo Verde: cadastro e edição de um lote.
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/../app/ecovila.php';
require_admin();

$pdo = db();
eco_ensure_schema();
$action = ($_GET['action'] ?? '') === 'new' ? 'new' : 'edit';
$id = (int) ($_GET['id'] ?? 0);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $postId = (int) ($_POST['id'] ?? 0);
    if (($_POST['form_action'] ?? '') === 'delete' && $postId) {
        $pdo->prepare('DELETE FROM eco_lots WHERE id = ?')->execute([$postId]);
        header('Location: /admin/ecovila.php?deleted=1');
        exit;
    }
    $str = fn(string $k, int $max) => mb_substr(trim(is_string($_POST[$k] ?? null) ? $_POST[$k] : ''), 0, $max);
    $data = [
        'number' => (int) ($_POST['number'] ?? 0),
        'title' => $str('title', 80),
        'status' => array_key_exists((string) ($_POST['status'] ?? ''), ECO_STATUSES) ? (string) $_POST['status'] : 'disponivel',
        'description' => $str('description', 3000),
        'image_url' => $str('image_url', 500),
        'is_visible' => !empty($_POST['is_visible']) ? 1 : 0,
    ];
    foreach (array_keys(ECO_SPEC_FIELDS) as $f) {
        $data[$f] = $str($f, 200);
        $data[$f . '_pending'] = !empty($_POST[$f . '_pending']) ? 1 : 0;
    }
    if ($data['title'] === '') $data['title'] = 'Lote ' . str_pad((string) $data['number'], 2, '0', STR_PAD_LEFT);
    try {
        if ($data['number'] < 1 || $data['number'] > 999) throw new RuntimeException('Informe o número do lote (1 a 999).');
        if ($data['image_url'] !== '' && !preg_match('#^(https://|assets/|/assets/)#i', $data['image_url'])) throw new RuntimeException('A imagem deve ser um caminho do site (ex.: assets/casa-verde.jpg) ou um endereço https://.');
        $cols = array_keys($data);
        if ($postId) {
            $sets = implode(', ', array_map(fn($c) => "$c = ?", $cols));
            $pdo->prepare("UPDATE eco_lots SET $sets, updated_at = NOW() WHERE id = ?")->execute([...array_values($data), $postId]);
            $savedId = $postId;
        } else {
            $s = $pdo->prepare('INSERT INTO eco_lots (' . implode(', ', $cols) . ') VALUES (' . implode(', ', array_fill(0, count($cols), '?')) . ') RETURNING id');
            $s->execute(array_values($data));
            $savedId = (int) $s->fetchColumn();
        }
        header('Location: /admin/ecovila-lote.php?action=edit&id=' . $savedId . '&saved=1');
        exit;
    } catch (PDOException $e) {
        $error = $e->getCode() === '23505' ? 'Já existe um lote com o número ' . $data['number'] . '.' : 'Não foi possível salvar o lote.';
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }
    $row = $data + ['id' => $postId];
    $action = $postId ? 'edit' : 'new';
}

if (!isset($row)) {
    $row = ['id' => 0, 'number' => '', 'title' => '', 'status' => 'disponivel', 'description' => '', 'image_url' => 'assets/casa-verde.jpg', 'is_visible' => 1];
    foreach (array_keys(ECO_SPEC_FIELDS) as $f) { $row[$f] = ''; $row[$f . '_pending'] = 0; }
    if ($action === 'new') $row['number'] = (int) $pdo->query('SELECT COALESCE(MAX(number), 0) + 1 FROM eco_lots')->fetchColumn();
    if ($action === 'edit') {
        $s = $pdo->prepare('SELECT * FROM eco_lots WHERE id = ?');
        $s->execute([$id]);
        $row = $s->fetch();
        if (!$row) { header('Location: /admin/ecovila.php'); exit; }
    }
}

admin_header($action === 'new' ? 'Novo lote' : 'Editar ' . $row['title']);
if (isset($_GET['saved'])): ?><p class="notice">Lote salvo. O site já mostra as novas informações.</p><?php endif;
if ($error): ?><p class="error"><?=h($error)?></p><?php endif; ?>

<p><a class="button light" href="/admin/ecovila.php">← Voltar aos lotes</a></p>

<section class="card">
  <form class="grid" method="post">
    <input type="hidden" name="csrf" value="<?=csrf()?>">
    <input type="hidden" name="id" value="<?=(int) $row['id']?>">

    <label>Número do lote<input type="number" name="number" min="1" max="999" value="<?=h((string) $row['number'])?>" required></label>
    <label>Nome exibido<input type="text" name="title" maxlength="80" value="<?=h($row['title'])?>" placeholder="Ex.: Lote 03"></label>
    <label>Status<select name="status">
      <?php foreach (ECO_STATUSES as $key => $label): ?><option value="<?=h($key)?>"<?=$row['status'] === $key ? ' selected' : ''?>><?=h($label)?></option><?php endforeach; ?>
    </select></label>
    <label class="eco-check"><input type="checkbox" name="is_visible" value="1"<?=$row['is_visible'] ? ' checked' : ''?>> Mostrar este lote no site</label>

    <label class="full">Texto de apresentação<textarea name="description" rows="3" maxlength="3000"><?=h($row['description'])?></textarea></label>

    <div class="full eco-specs">
      <h2>Ficha do lote</h2>
      <p class="muted">Marque <b>provisório</b> quando o valor ainda não for definitivo: o site mostra o campo em estilo de rascunho, para não ser lido como medida final.</p>
      <?php foreach (ECO_SPEC_FIELDS as $f => $label): ?>
        <div class="eco-spec-row">
          <label><?=h($label)?><input type="text" name="<?=$f?>" maxlength="200" value="<?=h($row[$f])?>"></label>
          <label class="eco-check"><input type="checkbox" name="<?=$f?>_pending" value="1"<?=$row[$f . '_pending'] ? ' checked' : ''?>> Provisório</label>
        </div>
      <?php endforeach; ?>
    </div>

    <label class="full">Imagem (sugestão de construção)
      <input type="text" name="image_url" id="eco-image" maxlength="500" value="<?=h($row['image_url'])?>" placeholder="assets/casa-verde.jpg ou https://...">
      <small class="field-hint">Imagens que já existem no site: <code>assets/casa-verde.jpg</code> (Casa Jardim) · <code>assets/casa-frente.jpg</code> (Casa Horizonte). Também aceita um endereço <code>https://</code>.</small>
    </label>
    <div class="full"><img id="eco-image-preview" class="eco-preview" src="<?=h(eco_image_preview((string) $row['image_url']))?>" alt="Pré-visualização da imagem"<?=$row['image_url'] ? '' : ' hidden'?>></div>

    <div class="full actions"><button>Salvar</button><a class="button light" href="/admin/ecovila.php">Cancelar</a></div>
  </form>
</section>

<?php if ($row['id']): ?>
<form method="post" onsubmit="return confirm(<?=h(json_encode('Excluir definitivamente o ' . $row['title'] . '? Ele deixa de aparecer no site.', JSON_UNESCAPED_UNICODE))?>)">
  <input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="form_action" value="delete"><input type="hidden" name="id" value="<?=(int) $row['id']?>">
  <button class="button danger">Excluir lote</button>
</form>
<?php endif; ?>

<script>
(function () {
  var input = document.getElementById('eco-image'), img = document.getElementById('eco-image-preview');
  input.addEventListener('change', function () {
    var v = input.value.trim();
    img.hidden = !v;
    if (v) img.src = /^https?:\/\//i.test(v) ? v : <?=json_encode(ECO_SITE_URL)?> + '/' + v.replace(/^\/+/, '');
  });
})();
</script>
<?php admin_footer();
