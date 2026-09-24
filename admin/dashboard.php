<?php
require_once __DIR__ . '/_layout.php';
$pdo = db();
$admin = current_admin();

function admin_sparkline_svg(array $values, int $w = 76, int $h = 30): string {
    $n = count($values);
    if ($n < 2) return '';
    $max = max(max($values), 1);
    $min = min($values);
    $range = max($max - $min, 1);
    $stepX = $w / ($n - 1);
    $points = [];
    foreach ($values as $i => $v) {
        $x = round($i * $stepX, 1);
        $y = round($h - (($v - $min) / $range) * ($h - 6) - 3, 1);
        $points[] = "$x,$y";
    }
    $line = 'M' . implode(' L', $points);
    $fill = $line . " L{$w},{$h} L0,{$h} Z";
    $last = explode(',', $points[count($points) - 1]);
    return '<svg class="kpi-spark" width="' . $w . '" height="' . $h . '" viewBox="0 0 ' . $w . ' ' . $h . '" aria-hidden="true">'
        . '<path class="spark-fill" d="' . h($fill) . '"/>'
        . '<path class="spark-line" d="' . h($line) . '"/>'
        . '<circle cx="' . $last[0] . '" cy="' . $last[1] . '" r="2.6"/>'
        . '</svg>';
}

function admin_time_ago(string $datetime): string {
    $diff = time() - strtotime($datetime . ' UTC');
    if ($diff < 60) return 'agora mesmo';
    if ($diff < 3600) return 'há ' . (int) floor($diff / 60) . ' min';
    if ($diff < 86400) return 'há ' . (int) floor($diff / 3600) . 'h';
    if ($diff < 172800) return 'ontem';
    if ($diff < 604800) return 'há ' . (int) floor($diff / 86400) . ' dias';
    return date('d/m/Y', strtotime($datetime . ' UTC'));
}

function admin_week_delta(PDO $pdo, string $table): array {
    try {
        $cur = (int) $pdo->query("SELECT COUNT(*) FROM {$table} WHERE created_at >= NOW() - INTERVAL '7 days'")->fetchColumn();
        $prev = (int) $pdo->query("SELECT COUNT(*) FROM {$table} WHERE created_at >= NOW() - INTERVAL '14 days' AND created_at < NOW() - INTERVAL '7 days'")->fetchColumn();
        return ['delta' => $cur - $prev];
    } catch (Throwable $e) { return ['delta' => 0]; }
}

function admin_daily_counts(PDO $pdo, string $table, int $days = 14): array {
    $counts = array_fill(0, $days, 0);
    try {
        $rows = $pdo->query("SELECT DATE(created_at) d, COUNT(*) c FROM {$table} WHERE created_at >= NOW() - INTERVAL '{$days} days' GROUP BY DATE(created_at)")->fetchAll();
        $today = new DateTime('today');
        foreach ($rows as $r) {
            $idx = $days - 1 - (int) $today->diff(new DateTime((string) $r['d']))->days;
            if ($idx >= 0 && $idx < $days) $counts[$idx] += (int) $r['c'];
        }
    } catch (Throwable $e) {}
    return $counts;
}

function admin_kpi_tile(string $icon, string $label, int $value, ?int $delta = null, ?array $sparkline = null): string {
    $deltaHtml = '';
    if ($delta !== null) {
        $cls = $delta > 0 ? 'up' : ($delta < 0 ? 'down' : 'flat');
        $arrow = $delta > 0 ? admin_icon('trending-up') : ($delta < 0 ? admin_icon('trending-down') : '');
        $sign = $delta > 0 ? '+' : '';
        $deltaHtml = '<span class="kpi-delta ' . $cls . '">' . $arrow . $sign . $delta . '/7d</span>';
    }
    $sparkHtml = $sparkline !== null ? admin_sparkline_svg($sparkline) : '<span></span>';
    return '<div class="kpi-tile"><div class="kpi-head"><span class="kpi-icon">' . admin_icon($icon) . '</span></div>'
        . '<div class="kpi-label">' . h($label) . '</div>'
        . '<div class="kpi-value">' . number_format($value, 0, ',', '.') . '</div>'
        . '<div class="kpi-foot">' . $deltaHtml . $sparkHtml . '</div></div>';
}

function admin_bar_chart(array $values, array $labels): string {
    $n = count($values);
    $w = 784; $h = 140;
    $max = max(max($values), 1);
    $slot = $w / $n;
    $barW = max(6, min(24, $slot - 8));
    $svg = '<svg class="activity-chart" viewBox="0 0 ' . $w . ' ' . ($h + 22) . '" preserveAspectRatio="none" role="img" aria-label="Atividade dos últimos 14 dias">';
    for ($g = 1; $g <= 3; $g++) {
        $gy = round($h - ($h * $g / 3), 1);
        $svg .= '<line class="gridline" x1="0" y1="' . $gy . '" x2="' . $w . '" y2="' . $gy . '"/>';
    }
    foreach ($values as $i => $v) {
        $bh = $v > 0 ? max(4, ($v / $max) * ($h - 10)) : 2;
        $x = round($i * $slot + ($slot - $barW) / 2, 1);
        $y = round($h - $bh, 1);
        $svg .= '<rect class="bar" tabindex="0" data-value="' . (int) $v . '" data-label="' . h($labels[$i]) . '" x="' . $x . '" y="' . $y . '" width="' . round($barW, 1) . '" height="' . round($bh, 1) . '" rx="4"/>';
        $svg .= '<text class="axis-label" x="' . round($x + $barW / 2, 1) . '" y="' . ($h + 16) . '" text-anchor="middle">' . h($labels[$i]) . '</text>';
    }
    $svg .= '</svg>';
    return $svg;
}

$counts = [];
foreach (['products' => 'Produtos', 'publications' => 'Publicações', 'blog_posts' => 'Posts no blog'] as $t => $n) {
    $counts[$n] = (int) $pdo->query("SELECT COUNT(*) FROM {$t}")->fetchColumn();
}
try { $catalogTotal = (int) $pdo->query('SELECT COUNT(*) FROM jd_catalog_items')->fetchColumn(); } catch (Throwable $e) { $catalogTotal = 0; }

$contactsTotal = (int) $pdo->query('SELECT COUNT(*) FROM contacts')->fetchColumn();
$contactsDelta = admin_week_delta($pdo, 'contacts')['delta'];
$contactsSpark = admin_daily_counts($pdo, 'contacts');

try { $briefingsNovo = (int) $pdo->query("SELECT COUNT(*) FROM briefings WHERE status='novo'")->fetchColumn(); } catch (Throwable $e) { $briefingsNovo = 0; }
$briefingsDelta = admin_week_delta($pdo, 'briefings')['delta'];
$briefingsSpark = admin_daily_counts($pdo, 'briefings');

$activitySeries = [];
$activityLabels = [];
for ($i = 0; $i < 14; $i++) {
    $activitySeries[$i] = ($contactsSpark[$i] ?? 0) + ($briefingsSpark[$i] ?? 0);
    $activityLabels[$i] = date('d/m', strtotime('-' . (13 - $i) . ' days'));
}

$leadsThisMonth = 0;
try {
    $leadsThisMonth = (int) $pdo->query("SELECT COUNT(*) FROM contacts WHERE created_at >= date_trunc('month', NOW())")->fetchColumn()
        + (int) $pdo->query("SELECT COUNT(*) FROM briefings WHERE created_at >= date_trunc('month', NOW())")->fetchColumn();
} catch (Throwable $e) {
    $leadsThisMonth = (int) $pdo->query("SELECT COUNT(*) FROM contacts WHERE created_at >= date_trunc('month', NOW())")->fetchColumn();
}

$feed = [];
$recentContacts = $pdo->query('SELECT name, subject, site, created_at FROM contacts ORDER BY created_at DESC LIMIT 6')->fetchAll();
foreach ($recentContacts as $row) {
    $feed[] = ['icon' => 'mail', 'color' => 'blue', 'title' => 'Novo contato de ' . $row['name'], 'sub' => $row['subject'] . ' · ' . (($row['site'] ?? 'gib') === 'jorgedadalt' ? 'Jorge Dadalt' : 'Global Invest'), 'time' => $row['created_at']];
}
try {
    $recentBriefings = $pdo->query('SELECT full_name, product, reference_code, created_at FROM briefings ORDER BY created_at DESC LIMIT 6')->fetchAll();
    foreach ($recentBriefings as $row) {
        $feed[] = ['icon' => 'clipboard', 'color' => 'orange', 'title' => 'Novo formulário de ' . $row['full_name'], 'sub' => ucfirst((string) $row['product']) . ' · ' . $row['reference_code'], 'time' => $row['created_at']];
    }
} catch (Throwable $e) {}
usort($feed, fn($a, $b) => strcmp((string) $b['time'], (string) $a['time']));
$feed = array_slice($feed, 0, 8);

$diasSemana = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
$meses = ['', 'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
$hoje = $diasSemana[(int) date('w')] . ', ' . (int) date('j') . ' de ' . $meses[(int) date('n')];
$hora = (int) date('G');
$saudacao = $hora < 12 ? 'Bom dia' : ($hora < 18 ? 'Boa tarde' : 'Boa noite');
$primeiroNome = explode(' ', trim((string) ($admin['name'] ?? '')))[0] ?? '';

admin_header('Painel principal');
?>
<div class="dash-hero">
  <div class="dash-hero-copy">
    <span><?=h($hoje)?></span>
    <h1><?=h($saudacao . ($primeiroNome !== '' ? ', ' . $primeiroNome : ''))?></h1>
    <p>Gestão direta dos dois sites: crie, edite, publique e acompanhe leads em um só lugar.</p>
  </div>
  <div class="dash-hero-figure">
    <b><?=number_format($leadsThisMonth, 0, ',', '.')?></b>
    <span>leads recebidos este mês</span>
  </div>
</div>

<div class="kpi-row">
  <?=admin_kpi_tile('mail', 'Contatos', $contactsTotal, $contactsDelta, $contactsSpark)?>
  <?=admin_kpi_tile('clipboard', 'Formulários novos', $briefingsNovo, $briefingsDelta, $briefingsSpark)?>
  <?=admin_kpi_tile('box', 'Produtos', $counts['Produtos'])?>
  <?=admin_kpi_tile('file-text', 'Publicações', $counts['Publicações'])?>
  <?=admin_kpi_tile('edit', 'Posts no blog', $counts['Posts no blog'])?>
  <?=admin_kpi_tile('book', 'Catálogo Jorge Dadalt', $catalogTotal)?>
</div>

<div class="dash-grid">
  <div>
    <section class="card chart-card">
      <div class="card-header">
        <div><h2>Atividade dos últimos 14 dias</h2><p class="muted">Contatos + formulários de projeto recebidos por dia, nos dois sites.</p></div>
        <span class="badge"><?=admin_icon('bar-chart')?> <?=array_sum($activitySeries)?> no período</span>
      </div>
      <div class="chart-wrap">
        <?=admin_bar_chart($activitySeries, $activityLabels)?>
        <div class="chart-tooltip"></div>
      </div>
    </section>

    <section class="card">
      <div class="card-header"><h2><?=admin_icon('clock')?> Atividade recente</h2></div>
      <div class="feed">
        <?php if (!$feed): ?><p class="feed-empty">Nenhuma atividade registrada ainda.</p><?php endif; ?>
        <?php foreach ($feed as $item): ?>
        <div class="feed-item">
          <span class="feed-icon <?=$item['color']?>"><?=admin_icon($item['icon'])?></span>
          <div class="feed-body"><b><?=h($item['title'])?></b><p><?=h($item['sub'])?></p></div>
          <span class="feed-time"><?=h(admin_time_ago((string) $item['time']))?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </section>
  </div>

  <div>
    <section class="card">
      <h2>Atalhos</h2>
      <div class="quick-grid" style="grid-template-columns:1fr">
        <a class="quick-card primary" href="/admin/manage.php?entity=products&action=new"><span class="kpi-icon"><?=admin_icon('plus-circle')?></span>Cadastrar produto</a>
        <a class="quick-card" href="/admin/manage.php?entity=publications&action=new"><span class="kpi-icon"><?=admin_icon('file-text')?></span>Nova publicação</a>
        <a class="quick-card" href="/admin/manage.php?entity=blog_posts&action=new"><span class="kpi-icon"><?=admin_icon('edit')?></span>Novo post no blog</a>
        <a class="quick-card" href="/admin/manage.php?entity=contacts&site=gib"><span class="kpi-icon"><?=admin_icon('mail')?></span>Gerenciar contatos</a>
        <a class="quick-card" href="/admin/briefings.php"><span class="kpi-icon"><?=admin_icon('clipboard')?></span>Formulários de projeto</a>
        <a class="quick-card" href="/admin/jd-catalog-edit.php?action=new"><span class="kpi-icon"><?=admin_icon('book')?></span>Item do catálogo Jorge Dadalt</a>
      </div>
    </section>
  </div>
</div>

<style>.dash-grid{display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start}@media(max-width:1050px){.dash-grid{grid-template-columns:1fr}}</style>
<script>
(function () {
  var chart = document.querySelector('.activity-chart');
  var tooltip = document.querySelector('.chart-tooltip');
  if (!chart || !tooltip) return;
  function showTip(bar) {
    var rect = bar.getBoundingClientRect();
    var wrapRect = chart.closest('.chart-wrap').getBoundingClientRect();
    var n = parseInt(bar.getAttribute('data-value'), 10);
    tooltip.textContent = bar.getAttribute('data-label') + ': ' + n + (n === 1 ? ' registro' : ' registros');
    tooltip.style.left = (rect.left + rect.width / 2 - wrapRect.left) + 'px';
    tooltip.style.top = (rect.top - wrapRect.top) + 'px';
    tooltip.classList.add('show');
  }
  chart.addEventListener('pointermove', function (e) {
    var bar = e.target.closest('.bar');
    if (bar) showTip(bar); else tooltip.classList.remove('show');
  });
  chart.addEventListener('pointerleave', function () { tooltip.classList.remove('show'); });
  chart.querySelectorAll('.bar').forEach(function (bar) {
    bar.addEventListener('focus', function () { showTip(bar); });
    bar.addEventListener('blur', function () { tooltip.classList.remove('show'); });
  });
})();
</script>

<?php admin_footer(); ?>
