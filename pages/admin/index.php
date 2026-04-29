<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
auth_admin();

$u = auth_user();
$adminPage  = 'dashboard';
$adminTitle = 'Dashboard';
$adminSub   = 'Bem-vinda, ' . (explode(' ', $u['name'] ?? 'Dra.')[1] ?? 'Dra.') . '! Aqui esta o resumo do dia.';
require_once __DIR__ . '/../../includes/admin_layout.php';
require_once __DIR__ . '/../../includes/tracker.php';

$dataDir = __DIR__ . '/../../data/';
$visitas = json_decode(@file_get_contents($dataDir . 'visitas.json'), true) ?? [];

$hoje = date('Y-m-d');
$agendHoje = $agendPend = $tosaHoje = $tosaPend = $mensNaoLidas = 0;

// Consultas: hoje + pendentes
$qAgend = gql('query {
    agendamentos(where:{data:{_eq:"' . $hoje . '"}}) { id status }
    ag_pend: agendamentos(where:{status:{_eq:"pendente"}}) { id }
}');
if ($qAgend['ok']) {
    $agendHoje = count($qAgend['data']['agendamentos']      ?? []);
    $agendPend = count($qAgend['data']['ag_pend']           ?? []);
}

// Tosagem: hoje + pendentes
$qTosa = gql('query {
    tosagem_agendamentos(where:{data:{_eq:"' . $hoje . '"}}) { id }
    tosa_pend: tosagem_agendamentos(where:{status:{_eq:"pendente"}}) { id }
}');
if ($qTosa['ok']) {
    $tosaHoje = count($qTosa['data']['tosagem_agendamentos'] ?? []);
    $tosaPend = count($qTosa['data']['tosa_pend']            ?? []);
}
$totalPend = $agendPend + $tosaPend;

// Mensagens nao lidas
$qMsg = gql('query { contatos(where:{lido:{_eq:false}}) { id } }');
if ($qMsg['ok']) $mensNaoLidas = count($qMsg['data']['contatos'] ?? []);

// Visitas
$visitasHoje  = $visitas['dias'][$hoje] ?? 0;
$visitasMes   = $visitas['meses'][date('Y-m')] ?? 0;
$visitasTotal = $visitas['total'] ?? 0;

// Ultimos 30 dias para grafico
$diasData = is_array($visitas['dias'] ?? null) ? $visitas['dias'] : [];
$ultimos30 = [];
for ($i = 29; $i >= 0; $i--) {
    $dk = date('Y-m-d', strtotime("-$i days"));
    $ultimos30[] = [
        'label' => date('d/m', strtotime($dk)),
        'date'  => $dk,
        'val'   => (int)($diasData[$dk] ?? 0),
    ];
}

// Proximas consultas + tosagem (combinadas)
$qProx = gql('query {
    consultas: agendamentos(
        where:{data:{_gte:"' . $hoje . '"},status:{_in:["pendente","confirmado"]}},
        order_by:{data:asc,horario:asc}, limit:6
    ) { id nome pet_nome servico data horario status }
    tosas: tosagem_agendamentos(
        where:{data:{_gte:"' . $hoje . '"},status:{_in:["pendente","confirmado"]}},
        order_by:{data:asc,horario:asc}, limit:4
    ) { id nome pet_nome servico data horario status }
}');

$proxConsultas = array_filter($qProx['data']['consultas'] ?? [], 'is_array');
$proxTosas     = array_filter($qProx['data']['tosas']     ?? [], 'is_array');

// JSON para Chart.js
$chartLabels = json_encode(array_column($ultimos30, 'label'));
$chartData   = json_encode(array_column($ultimos30, 'val'));
$maxVal      = max(array_column($ultimos30, 'val') ?: [1]);
?>

<!-- STAT CARDS -->
<div class="stats-grid" style="margin-bottom:28px;">
    <div class="stat-card green">
        <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
        <div class="stat-info"><strong><?= $agendHoje ?></strong><span>Consultas Hoje</span></div>
    </div>
    <div class="stat-card orange">
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
        <div class="stat-info"><strong><?= $totalPend ?></strong><span>Aguardando Confirmacao</span></div>
    </div>
    <div class="stat-card blue">
        <div class="stat-icon"><i class="fas fa-scissors"></i></div>
        <div class="stat-info"><strong><?= $tosaHoje ?></strong><span>Banho &amp; Tosa Hoje</span></div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon"><i class="fas fa-envelope"></i></div>
        <div class="stat-info"><strong><?= $mensNaoLidas ?></strong><span>Mensagens Nao Lidas</span></div>
    </div>
    <div class="stat-card teal">
        <div class="stat-icon"><i class="fas fa-eye"></i></div>
        <div class="stat-info"><strong><?= $visitasHoje ?></strong><span>Visitas Hoje</span></div>
    </div>
</div>

<!-- GRAFICO DE VISITAS + TABELA -->
<div class="panel-card" style="margin-bottom:28px;">
    <div class="panel-card-header">
        <h2><i class="fas fa-chart-area"></i> Visitas ao Site – Ultimos 30 dias</h2>
        <div style="display:flex;gap:16px;font-size:.82rem;color:var(--text-light);">
            <span><i class="fas fa-calendar-alt" style="color:var(--primary)"></i> Este mes: <strong style="color:var(--text)"><?= number_format($visitasMes) ?></strong></span>
            <span><i class="fas fa-globe" style="color:var(--primary)"></i> Total: <strong style="color:var(--text)"><?= number_format($visitasTotal) ?></strong></span>
        </div>
    </div>
    <div class="panel-card-body" style="padding:0;">
        <div style="display:grid;grid-template-columns:1fr 320px;min-height:340px;">

            <!-- CHART.JS -->
            <div style="padding:24px;border-right:1px solid var(--bg2);">
                <canvas id="visitasChart" style="width:100%;max-height:290px;"></canvas>
            </div>

            <!-- TABELA DOS ULTIMOS 14 DIAS -->
            <div style="overflow-y:auto;max-height:340px;">
                <table class="data-table" style="font-size:.82rem;">
                    <thead style="position:sticky;top:0;z-index:1;">
                        <tr><th>Data</th><th>Dia</th><th style="text-align:right;">Visitas</th></tr>
                    </thead>
                    <tbody>
                    <?php
                    $dias14 = array_slice(array_reverse($ultimos30), 0, 14);
                    foreach ($dias14 as $d):
                        $pct = $maxVal > 0 ? round(($d['val'] / $maxVal) * 100) : 0;
                    ?>
                    <tr>
                        <td style="white-space:nowrap;"><?= date('d/m/Y', strtotime($d['date'])) ?></td>
                        <td style="color:var(--text-light);"><?= date('D', strtotime($d['date'])) ?></td>
                        <td style="text-align:right;">
                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:8px;">
                                <div style="width:50px;height:6px;background:var(--bg2);border-radius:3px;overflow:hidden;">
                                    <div style="width:<?= $pct ?>%;height:100%;background:var(--primary);border-radius:3px;"></div>
                                </div>
                                <strong style="color:var(--primary);min-width:24px;"><?= $d['val'] ?></strong>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- PROXIMAS CONSULTAS + PROXIMAS TOSAGENS -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:28px;">

    <!-- PROXIMAS CONSULTAS -->
    <div class="panel-card">
        <div class="panel-card-header">
            <h2><i class="fas fa-stethoscope"></i> Proximas Consultas</h2>
            <a href="/pages/admin/agendamentos.php" class="btn btn-outline btn-sm">Ver todos</a>
        </div>
        <div class="panel-card-body" style="padding:0;">
            <?php if (empty($proxConsultas)): ?>
                <p style="padding:24px;color:var(--text-light);font-size:.88rem;">Nenhuma consulta agendada.</p>
            <?php else: ?>
            <table class="data-table" style="font-size:.83rem;">
                <thead><tr><th>Data</th><th>Paciente</th><th>Servico</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($proxConsultas as $a): ?>
                <tr>
                    <td><?= date('d/m', strtotime($a['data'])) ?><br><small style="color:var(--text-light)"><?= $a['horario'] ?? '' ?></small></td>
                    <td><?= htmlspecialchars($a['nome']) ?><br><small style="color:var(--text-light)"><?= htmlspecialchars($a['pet_nome']) ?></small></td>
                    <td style="font-size:.78rem;"><?= htmlspecialchars($a['servico'] ?? '') ?></td>
                    <td><span class="badge badge-<?= $a['status'] ?>"><?= ucfirst($a['status']) ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- PROXIMAS TOSAGENS -->
    <div class="panel-card">
        <div class="panel-card-header">
            <h2><i class="fas fa-scissors"></i> Proximos Banho &amp; Tosa</h2>
            <a href="/pages/admin/tosagem_list.php" class="btn btn-outline btn-sm">Ver todos</a>
        </div>
        <div class="panel-card-body" style="padding:0;">
            <?php if (empty($proxTosas)): ?>
                <p style="padding:24px;color:var(--text-light);font-size:.88rem;">Nenhuma tosa agendada.</p>
            <?php else: ?>
            <table class="data-table" style="font-size:.83rem;">
                <thead><tr><th>Data</th><th>Tutor / Pet</th><th>Servico</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($proxTosas as $a): ?>
                <tr>
                    <td><?= date('d/m', strtotime($a['data'])) ?><br><small style="color:var(--text-light)"><?= $a['horario'] ?? '' ?></small></td>
                    <td><?= htmlspecialchars($a['nome']) ?><br><small style="color:var(--text-light)"><?= htmlspecialchars($a['pet_nome']) ?></small></td>
                    <td style="font-size:.78rem;"><?= htmlspecialchars($a['servico'] ?? '') ?></td>
                    <td><span class="badge badge-<?= $a['status'] ?>"><?= ucfirst($a['status']) ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- ATALHOS -->
<div class="panel-card">
    <div class="panel-card-header"><h2><i class="fas fa-link"></i> Atalhos Rapidos</h2></div>
    <div class="panel-card-body" style="display:flex;gap:12px;flex-wrap:wrap;">
        <a href="/pages/admin/horarios.php"    class="btn btn-outline"><i class="fas fa-clock"        style="color:var(--primary)"></i> Editar Horarios</a>
        <a href="/pages/admin/precos.php"      class="btn btn-outline"><i class="fas fa-tags"         style="color:var(--primary)"></i> Editar Precos</a>
        <a href="/pages/admin/mensagens.php"   class="btn btn-outline"><i class="fas fa-envelope"     style="color:var(--primary)"></i> Mensagens <?= $mensNaoLidas ? '<span class="badge badge-pendente">'.$mensNaoLidas.'</span>' : '' ?></a>
        <a href="/pages/admin/produtos.php"    class="btn btn-outline"><i class="fas fa-shopping-bag" style="color:var(--primary)"></i> Produtos da Loja</a>
        <a href="/pages/admin/visitas.php"     class="btn btn-outline"><i class="fas fa-eye"          style="color:var(--primary)"></i> Relatorio de Visitas</a>
        <a href="/index.php" target="_blank"   class="btn btn-primary" style="margin-left:auto;"><i class="fas fa-external-link-alt"></i> Ver Site</a>
    </div>
</div>

<!-- CHART.JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    const ctx    = document.getElementById('visitasChart');
    if (!ctx) return;
    const labels = <?= $chartLabels ?>;
    const data   = <?= $chartData ?>;
    const grad   = ctx.getContext('2d').createLinearGradient(0, 0, 0, 290);
    grad.addColorStop(0,   'rgba(46,158,107,.35)');
    grad.addColorStop(1,   'rgba(46,158,107,0)');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Visitas',
                data,
                borderColor: '#2e9e6b',
                borderWidth: 2.5,
                backgroundColor: grad,
                fill: true,
                tension: 0.45,
                pointBackgroundColor: '#2e9e6b',
                pointRadius: 3,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1a2e25',
                    titleColor: '#fff',
                    bodyColor: 'rgba(255,255,255,.8)',
                    padding: 12,
                    cornerRadius: 10,
                    callbacks: {
                        label: ctx => ' ' + ctx.parsed.y + ' visita(s)',
                    }
                },
            },
            scales: {
                x: {
                    grid: { color: 'rgba(0,0,0,.04)' },
                    ticks: { font: { size: 10 }, color: '#6b7f8e', maxTicksLimit: 10 },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,.06)' },
                    ticks: { font: { size: 11 }, color: '#6b7f8e', stepSize: 1 },
                }
            }
        }
    });
})();
</script>

<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>
