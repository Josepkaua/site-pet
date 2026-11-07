<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
auth_check();

$u = auth_user();
$pageTitle = 'Meu Painel – ' . CLINIC_NAME;
$h = horarios();
$cls = fn($st) => $st === 'fechado' ? 'status-closed' : ($st === 'limitado' ? 'status-limit' : 'status-open');
$hor = fn($r) => (($r['status'] ?? '') === 'fechado' || empty($r['abertura'])) ? 'Fechado' : $r['abertura'] . ' – ' . $r['fechamento'];

require __DIR__ . '/../../includes/header.php';
?>

<div class="painel-wrap">
    <div class="painel-header anim-up">
        <div>
            <h1>Bem-vindo(a), <?= e(explode(' ', $u['name'])[0]) ?>! 👋</h1>
            <p>Aqui você pode agendar atendimentos e consultar informações da clínica.</p>
        </div>
        <div class="painel-header-actions">
            <?php if (is_admin()): ?>
            <a href="/pages/admin/index.php" class="btn btn-primary btn-sm"><i class="fas fa-arrow-left"></i> Painel Admin</a>
            <?php endif; ?>
            <a href="/pages/logout.php" class="btn btn-outline btn-sm"><i class="fas fa-sign-out-alt"></i> Sair</a>
        </div>
    </div>

    <div class="painel-cards" data-reveal-group>
        <a href="/pages/consultas.php" class="service-card reveal">
            <div class="service-icon"><i class="fas fa-calendar-check"></i></div>
            <h3>Agendar Consulta</h3>
            <p>Marque sua próxima consulta com a <?= e(CLINIC_DRA) ?>.</p>
        </a>
        <a href="/pages/tosagem.php" class="service-card reveal">
            <div class="service-icon"><i class="fas fa-scissors"></i></div>
            <h3>Agendar Banho &amp; Tosa</h3>
            <p>Deixe seu pet sempre cheiroso e arrumado.</p>
        </a>
        <a href="/pages/loja.php" class="service-card reveal">
            <div class="service-icon"><i class="fas fa-bag-shopping"></i></div>
            <h3>Loja Pet</h3>
            <p>Produtos para o seu animal de estimação.</p>
        </a>
        <a href="/pages/contato.php" class="service-card reveal">
            <div class="service-icon"><i class="fas fa-envelope"></i></div>
            <h3>Falar com a Clínica</h3>
            <p>Tire dúvidas ou envie uma mensagem.</p>
        </a>
    </div>

    <div class="schedule-card reveal">
        <div class="schedule-card-header"><i class="fas fa-clock"></i><h3>Horários de Atendimento</h3></div>
        <table class="schedule-table">
            <thead><tr><th>Dia</th><th>Consultas</th><th>Banho &amp; Tosa</th></tr></thead>
            <tbody>
            <?php foreach ($h['consultas'] ?? [] as $i => $c): $t = $h['tosagem'][$i] ?? []; ?>
            <tr <?= (int)date('N') === $i + 1 ? 'class="is-today"' : '' ?>>
                <td><?= e($c['dia']) ?></td>
                <td class="<?= $cls($c['status'] ?? '') ?>"><?= e($hor($c)) ?></td>
                <td class="<?= $cls($t['status'] ?? '') ?>"><?= e($hor($t)) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
