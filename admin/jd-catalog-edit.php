<?php
require_once __DIR__ . '/_layout.php';
$pdo = db();

$categoryLabels = ['livros-e-ebooks' => 'Livros e E-books', 'cursos-e-mentorias' => 'Cursos e Mentorias', 'negocios-digitais' => 'Negócios Digitais'];
$typeLabels = ['livro' => 'Livro', 'ebook' => 'E-book', 'curso' => 'Curso', 'mentoria' => 'Mentoria', 'negocio' => 'Negócio'];

function jd_clean_list(array $values): array {
    $out = [];
    foreach ($values as $v) { $v = trim((string) $v); if ($v !== '') $out[] = $v; }
    return $out;
}
function jd_clean_triples($rows): array {
    if (!is_array($rows)) return [];
    ksort($rows);
    $out = [];
    foreach ($rows as $row) {
        $a = trim((string) ($row[0] ?? ''));
        $b = trim((string) ($row[1] ?? ''));
        $c = trim((string) ($row[2] ?? ''));
        if ($a === '' && $b === '' && $c === '') continue;
        $out[] = [$a, $b, $c];
    }
    return $out;
}

$action = $_GET['action'] ?? 'new';
$id = (int) ($_GET['id'] ?? 0);
$notice = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    $isNew = $id === 0;
    try {
        $category = (string) ($_POST['category'] ?? '');
        $itemType = (string) ($_POST['item_type'] ?? '');
        $title = trim((string) ($_POST['title'] ?? ''));
        $slug = trim((string) ($_POST['slug'] ?? ''));
        $urlSlug = trim((string) ($_POST['url_slug'] ?? ''));
        $ctaHref = trim((string) ($_POST['cta_href'] ?? ''));
        if (!isset($categoryLabels[$category])) throw new RuntimeException('Selecione uma categoria válida.');
        if (!isset($typeLabels[$itemType])) throw new RuntimeException('Selecione um tipo válido.');
        if ($title === '' || $slug === '' || $urlSlug === '' || $ctaHref === '') throw new RuntimeException('Preencha título, slug, slug da URL e link/ação (CTA).');

        $data = [
            'category' => $category, 'item_type' => $itemType, 'slug' => $slug, 'url_slug' => $urlSlug,
            'type_label' => trim((string) ($_POST['type_label'] ?? '')),
            'title' => $title,
            'image_url' => trim((string) ($_POST['image_url'] ?? '')),
            'headline' => trim((string) ($_POST['headline'] ?? '')),
            'lead' => trim((string) ($_POST['lead'] ?? '')),
            'list_summary' => trim((string) ($_POST['list_summary'] ?? '')),
            'price' => trim((string) ($_POST['price'] ?? '')) ?: null,
            'cta_label' => trim((string) ($_POST['cta_label'] ?? '')) ?: 'Saiba mais',
            'cta_href' => $ctaHref,
            'question' => trim((string) ($_POST['question'] ?? '')),
            'format_label' => trim((string) ($_POST['format_label'] ?? '')) ?: null,
            'format_answer' => trim((string) ($_POST['format_answer'] ?? '')) ?: null,
            'stakes' => trim((string) ($_POST['stakes'] ?? '')) ?: null,
            'status' => in_array($_POST['status'] ?? '', ['draft', 'published'], true) ? $_POST['status'] : 'draft',
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'points' => json_encode(jd_clean_list($_POST['points'] ?? []), JSON_UNESCAPED_UNICODE),
            'audience' => json_encode(jd_clean_list($_POST['audience'] ?? []), JSON_UNESCAPED_UNICODE),
            'story' => json_encode(jd_clean_list($_POST['story'] ?? []), JSON_UNESCAPED_UNICODE),
            'trust' => json_encode(jd_clean_triples($_POST['trust'] ?? []), JSON_UNESCAPED_UNICODE),
            'outcomes' => json_encode(jd_clean_triples($_POST['outcomes'] ?? []), JSON_UNESCAPED_UNICODE),
            'journey' => json_encode(jd_clean_triples($_POST['journey'] ?? []), JSON_UNESCAPED_UNICODE),
        ];

        if ($isNew) {
            $keys = array_keys($data);
            $stmt = $pdo->prepare('INSERT INTO jd_catalog_items (' . implode(',', $keys) . ') VALUES (' . implode(',', array_fill(0, count($keys), '?')) . ') RETURNING id');
            $stmt->execute(array_values($data));
            $newId = (int) $stmt->fetch()['id'];
            header('Location: /admin/jd-catalog-edit.php?action=edit&id=' . $newId . '&saved=1');
            exit;
        } else {
            $sets = implode(',', array_map(fn($k) => "$k=?", array_keys($data)));
            $sets .= ',updated_at=NOW()';
            $pdo->prepare("UPDATE jd_catalog_items SET $sets WHERE id=?")->execute([...array_values($data), $id]);
            header('Location: /admin/jd-catalog-edit.php?action=edit&id=' . $id . '&saved=1');
            exit;
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$row = [
    'category' => '', 'item_type' => '', 'slug' => '', 'url_slug' => '', 'type_label' => '', 'title' => '',
    'image_url' => '', 'headline' => '', 'lead' => '', 'list_summary' => '', 'price' => '', 'cta_label' => 'Saiba mais',
    'cta_href' => '', 'question' => '', 'format_label' => '', 'format_answer' => '', 'stakes' => '', 'status' => 'draft',
    'sort_order' => 0, 'points' => '[]', 'audience' => '[]', 'story' => '[]', 'trust' => '[]', 'outcomes' => '[]', 'journey' => '[]',
];
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare('SELECT * FROM jd_catalog_items WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if ($found) $row = $found;
}
if (isset($_GET['saved'])) $notice = 'Item salvo com sucesso.';

$points = json_decode((string) $row['points'], true) ?: [];
$audience = json_decode((string) $row['audience'], true) ?: [];
$story = json_decode((string) $row['story'], true) ?: [];
$trust = json_decode((string) $row['trust'], true) ?: [];
$outcomes = json_decode((string) $row['outcomes'], true) ?: [];
$journey = json_decode((string) $row['journey'], true) ?: [];

function jd_list_repeater(string $name, string $label, array $values, bool $textarea = false): void {
    ?>
    <div class="field full">
      <label><?=h($label)?></label>
      <div class="repeater" data-repeater-list="<?=$name?>">
        <?php foreach ($values as $v): ?>
        <div class="repeater-row">
          <?php if ($textarea): ?><textarea name="<?=$name?>[]" rows="2"><?=h($v)?></textarea><?php else: ?><input type="text" name="<?=$name?>[]" value="<?=h($v)?>"><?php endif; ?>
          <button type="button" class="button danger repeater-remove">Remover</button>
        </div>
        <?php endforeach; ?>
      </div>
      <template data-repeater-template="<?=$name?>"><div class="repeater-row"><?php if ($textarea): ?><textarea name="<?=$name?>[]" rows="2"></textarea><?php else: ?><input type="text" name="<?=$name?>[]" value=""><?php endif; ?><button type="button" class="button danger repeater-remove">Remover</button></div></template>
      <button type="button" class="button light repeater-add" data-repeater-add="<?=$name?>" style="margin-top:8px">+ Adicionar</button>
    </div>
    <?php
}

function jd_triple_repeater(string $name, string $label, array $rows, array $placeholders): void {
    ?>
    <div class="field full">
      <label><?=h($label)?></label>
      <div class="repeater" data-repeater-list="<?=$name?>" data-next-index="<?=count($rows)?>">
        <?php foreach ($rows as $i => $r): ?>
        <div class="repeater-row">
          <div class="repeater-row-triple">
            <input type="text" name="<?=$name?>[<?=$i?>][0]" placeholder="<?=h($placeholders[0])?>" value="<?=h($r[0] ?? '')?>">
            <input type="text" name="<?=$name?>[<?=$i?>][1]" placeholder="<?=h($placeholders[1])?>" value="<?=h($r[1] ?? '')?>">
            <input type="text" name="<?=$name?>[<?=$i?>][2]" placeholder="<?=h($placeholders[2])?>" value="<?=h($r[2] ?? '')?>">
          </div>
          <button type="button" class="button danger repeater-remove">Remover</button>
        </div>
        <?php endforeach; ?>
      </div>
      <template data-repeater-template="<?=$name?>"><div class="repeater-row"><div class="repeater-row-triple"><input type="text" name="<?=$name?>[__INDEX__][0]" placeholder="<?=h($placeholders[0])?>" value=""><input type="text" name="<?=$name?>[__INDEX__][1]" placeholder="<?=h($placeholders[1])?>" value=""><input type="text" name="<?=$name?>[__INDEX__][2]" placeholder="<?=h($placeholders[2])?>" value=""></div><button type="button" class="button danger repeater-remove">Remover</button></div></template>
      <button type="button" class="button light repeater-add" data-repeater-add="<?=$name?>" style="margin-top:8px">+ Adicionar</button>
    </div>
    <?php
}

admin_header($action === 'new' ? 'Novo item do catálogo' : 'Editar item do catálogo');
if ($notice): ?><p class="notice"><?=h($notice)?></p><?php endif;
if ($error): ?><p class="error"><?=h($error)?></p><?php endif; ?>

<section class="card">
  <form class="grid" method="post" id="jd-form">
    <input type="hidden" name="csrf" value="<?=csrf()?>">
    <input type="hidden" name="id" value="<?=h((string) ($row['id'] ?? ''))?>">

    <label>Título<input type="text" name="title" id="jd_title" value="<?=h($row['title'])?>" required></label>
    <label>Slug (identificador interno, usado pela página de detalhe)<input type="text" name="slug" id="jd_slug" value="<?=h($row['slug'])?>" required></label>
    <label>Slug da URL (pasta real em jorgedadalt.com)<input type="text" name="url_slug" id="jd_url_slug" value="<?=h($row['url_slug'])?>" required></label>
    <label>Categoria<select name="category" id="jd_category" required>
      <option value="">Selecione</option>
      <?php foreach ($categoryLabels as $key => $label): ?><option value="<?=$key?>" <?=$row['category']===$key?'selected':''?>><?=h($label)?></option><?php endforeach; ?>
    </select></label>
    <label>Tipo<select name="item_type" id="jd_item_type" required>
      <option value="">Selecione</option>
      <?php foreach ($typeLabels as $key => $label): ?><option value="<?=$key?>" <?=$row['item_type']===$key?'selected':''?>><?=h($label)?></option><?php endforeach; ?>
    </select></label>
    <label>Rótulo do tipo (aparece na página)<input type="text" name="type_label" value="<?=h($row['type_label'])?>" placeholder="Ex.: Livro físico, Mentoria 01"></label>
    <label>Status<select name="status">
      <option value="draft" <?=$row['status']==='draft'?'selected':''?>>Rascunho</option>
      <option value="published" <?=$row['status']==='published'?'selected':''?>>Publicado</option>
    </select></label>
    <label>Ordem de exibição<input type="number" name="sort_order" value="<?=h((string) $row['sort_order'])?>"></label>

    <label class="full">URL da imagem<input type="text" name="image_url" value="<?=h($row['image_url'])?>" placeholder="/images/arquivo.png"></label>
    <label class="full">Chamada principal (headline, aceita <code>&lt;em&gt;</code>)<input type="text" name="headline" value="<?=h($row['headline'])?>"></label>
    <label class="full">Texto de apoio (lead)<textarea name="lead" rows="3"><?=h($row['lead'])?></textarea></label>
    <label class="full">Resumo curto (usado nas páginas de listagem)<textarea name="list_summary" rows="2"><?=h($row['list_summary'])?></textarea></label>
    <label>Preço / condição<input type="text" name="price" value="<?=h((string) $row['price'])?>" placeholder="Ex.: R$ 59,90 ou Edição digital"></label>
    <label>Texto do botão (CTA)<input type="text" name="cta_label" value="<?=h($row['cta_label'])?>"></label>
    <label class="full">Link do botão (CTA)<input type="text" name="cta_href" value="<?=h($row['cta_href'])?>" placeholder="https://... ou /contato?assunto=..." required></label>

    <?php jd_list_repeater('points', 'Pontos principais', $points); ?>

    <div data-type-group="livro,ebook" style="display:contents">
      <?php jd_triple_repeater('trust', 'Prova social (3 selos)', $trust, ['Número', 'Título', 'Descrição']); ?>
    </div>

    <label class="full">Pergunta de abertura<input type="text" name="question" value="<?=h($row['question'])?>"></label>
    <?php jd_list_repeater('story', 'História (um parágrafo por linha)', $story, true); ?>
    <?php jd_triple_repeater('outcomes', 'Resultados / benefícios', $outcomes, ['Número', 'Título', 'Descrição']); ?>
    <?php jd_list_repeater('audience', 'Para quem é', $audience); ?>

    <div data-type-group="livro,ebook" style="display:contents">
      <label class="full">Formato (rótulo)<input type="text" name="format_label" value="<?=h((string) $row['format_label'])?>"></label>
      <label class="full">Formato (resposta do FAQ)<textarea name="format_answer" rows="2"><?=h((string) $row['format_answer'])?></textarea></label>
    </div>

    <div data-type-group="mentoria" style="display:contents">
      <label class="full">Frase de impacto (stakes)<textarea name="stakes" rows="2"><?=h((string) $row['stakes'])?></textarea></label>
      <?php jd_triple_repeater('journey', 'Jornada (4 passos)', $journey, ['Nº', 'Título', 'Descrição']); ?>
    </div>

    <div class="full actions">
      <button>Salvar</button>
      <a class="button light" href="/admin/jd-catalog.php">Cancelar</a>
    </div>
  </form>
</section>

<script src="/admin/admin-repeater.js"></script>
<script>
(function () {
  var title = document.getElementById('jd_title'), slug = document.getElementById('jd_slug'), urlSlug = document.getElementById('jd_url_slug');
  function slugify(v) { return v.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, ''); }
  if (slug.value.trim()) slug.dataset.touched = '1';
  if (urlSlug.value.trim()) urlSlug.dataset.touched = '1';
  slug.addEventListener('input', function () { slug.dataset.touched = '1'; });
  urlSlug.addEventListener('input', function () { urlSlug.dataset.touched = '1'; });
  title.addEventListener('input', function () {
    if (!slug.dataset.touched) slug.value = slugify(title.value);
    if (!urlSlug.dataset.touched) urlSlug.value = slugify(title.value);
  });

  var itemType = document.getElementById('jd_item_type');
  var groups = document.querySelectorAll('[data-type-group]');
  function updateGroups() {
    var t = itemType.value;
    groups.forEach(function (g) {
      var allowed = g.getAttribute('data-type-group').split(',');
      g.style.display = allowed.indexOf(t) !== -1 ? 'contents' : 'none';
    });
  }
  itemType.addEventListener('change', updateGroups);
  updateGroups();
})();
</script>

<?php admin_footer(); ?>
