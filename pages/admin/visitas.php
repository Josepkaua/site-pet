<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
auth_admin();

$adminPage  = 'visitas';
$adminTitle = 'Visitas ao Site';
$adminSub   = 'Acompanhe o tráfego do seu site em tempo real';
require_once __DIR__ . '/../../includes/admin_layout.php';

$file    = __DIR__ . '/../../data/visitas.json';
$visitas = json_decode(@file_get_contents($file), true) ?? [];

$hoje   = date('Y-m-d');
$mes    = date('Y-m');
$total  = $visitas['total'] ?? 0;
$hj     = $visitas['dias'][$hoje] ?? 0;
$meAtual= $visitas['meses'][$mes] ?? 0;

// Ordenar dias desc
$dias = $visitas['dias'] ?? [];
krsort($dias);
$dias30 = array_slice($dias, 0, 30, true);

// Gráfico 14 dias
$diasData  = is_array($visitas['dias'] ?? null) ? $visitas['dias'] : [];
$ultimos14 = [];
for ($i = 13; $i >= 0; $i--) {
    $dataKey    = date('Y-m-d', strtotime("-$i days"));
    $ultimos14[] = ['label' => date('d/m', strtotime($dataKey)), 'val' => (int)($diasData[$dataKey] ?? 0)];
}
$maxVal = !empty($ultimos14) ? (max(array_column($ultimos14, 'val')) ?: 1) : 1;

// Top páginas
$paginas = $visitas['paginas'] ?? [];
arsort($paginas);
$paginas = array_slice($paginas, 0, 10, true);

// Meses
$meses = $visitas['meses'] ?? [];
krsort($meses);
$meses = array_slice($meses, 0, 12, true);
?>

<!-- STATS -->
<div class="stats-grid" style="margin-bottom:28px;">
    <div class="stat-card teal">
        <div class="stat-icon"><i class="fas fa-eye"></i></div>
        <div class="stat-info"><strong><?= number_format($hj) ?></strong><span>Visitas Hoje</span></div>
    </div>
    <div class="stat-card blue">
        <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
        <div class="stat-info"><strong><?= number_format($meAtual) ?></strong><span>Este Mês</span></div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon"><i class="fas fa-globe"></i></div>
        <div class="stat-info"><strong><?= number_format($total) ?></strong><span>Total de Visitas</span></div>
    </div>
    <div class="stat-card orange">
        <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
        <div class="stat-info">
            <strong><?= count($dias) > 0 ? round(array_sum($dias) / count($dias), 1) : 0 ?></strong>
            <span>Média / Dia</span>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:24px;">

    <!-- GRÁFICO 14 DIAS -->
    <div class="panel-card">
        <div class="panel-card-header">
            <h2><i class="fas fa-chart-bar"></i> Últimos 14 dias</h2>
        </div>
        <div class="panel-card-body">
            <div class="chart-bars" style="height:160px;">
                <?php foreach ($ultimos14 as $bar): ?>
                <div class="chart-bar-wrap">
                    <span style="font-size:.68rem;color:var(--text-light);margin-bottom:2px;"><?= $bar['val'] ?: '' ?></span>
                    <div class="chart-bar"
                         style="height:<?= $bar['val'] ? round(($bar['val']/$maxVal)*140) : 4 ?>px;"
                         title="<?= $bar['val'] ?> visita(s) em <?= $bar['label'] ?>"></div>
                    <span class="chart-label"><?= $bar['label'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- TOP PÁGINAS -->
    <div class="panel-card">
        <div class="panel-card-header">
            <h2><i class="fas fa-file-alt"></i> Páginas Mais Visitadas</h2>
        </div>
        <div class="panel-card-body" style="padding:0;">
            <?php  if (empty($paginas)): ?>
                <p style="padding:24px;color:var(--text-light);font-size:.88rem;">Nenhuma visita registrada ainda.</p>
            <?php  else: ?>
            <table class="data-table">
                <thead><tr><th>Página</th><th style="text-align:right;">Visitas</th></tr></thead>
                <tbody>
                <?php  foreach ($paginas as $pg => $cnt): ?>
                <tr>
                    <td style="font-size:.85rem;"><?= htmlspecialchars($pg) ?></td>
                    <td style="text-align:right;font-weight:600;color:var(--primary);"><?= number_format($cnt) ?></td>
                </tr>
                <?php  endforeach; ?>
                </tbody>
            </table>
            <?php  endif; ?>
        </div>
    </div>
</div>

<!-- HISTÓRICO 30 DIAS -->
<div class="panel-card">
    <div class="panel-card-header">
        <h2><i class="fas fa-history"></i> Histórico por Dia (últimos 30 dias)</h2>
    </div>
    <div class="panel-card-body" style="padding:0;overflow-x:auto;">
        <table class="data-table">
            <thead><tr><th>Data</th><th>Dia da Semana</th><th style="text-align:right;">Visitas</th></tr></thead>
            <tbody>
            <?php foreach ($dias30 as $dateKey => $cnt): ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($dateKey)) ?></td>
                <td style="color:var(--text-light);"><?= date('l', strtotime($dateKey)) ?></td>
                <td style="text-align:right;font-weight:600;color:var(--primary);"><?= number_format($cnt) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php  if (empty($dias30)): ?>
            <tr><td colspan="3" style="text-align:center;color:var(--text-light);padding:24px;">Nenhuma visita registrada. O rastreamento começa automaticamente quando clientes acessam o site.</td></tr>
            <?php  endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php  require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>
