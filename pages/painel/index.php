<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
auth_check();

$u = auth_user();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Painel – <?= CLINIC_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/Site-pet/assets/css/style.css">
    <style>
        .painel-wrap { max-width: 860px; margin: 60px auto; padding: 0 24px; }
        .painel-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:32px; flex-wrap:wrap; gap:12px; }
        .painel-header h1 { font-size:1.5rem; font-weight:700; }
        .painel-header p { color:var(--text-light); font-size:.9rem; }
        .painel-cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:20px; margin-bottom:32px; }
    </style>
</head>
<body style="background:var(--bg);">

<nav class="navbar">
    <div class="nav-container">
        <a href="/Site-pet/index.php" class="nav-logo"><i class="fas fa-paw"></i> <span><?= CLINIC_NAME ?></span></a>
        <div style="display:flex;align-items:center;gap:16px;">
            <span style="font-size:.85rem;color:var(--text-light);">Olá, <strong><?= htmlspecialchars($u['name']) ?></strong></span>
            <a href="/Site-pet/pages/logout.php" class="btn btn-outline btn-sm"><i class="fas fa-sign-out-alt"></i> Sair</a>
        </div>
    </div>
</nav>

<div class="painel-wrap">
    <div class="painel-header">
        <div>
            <h1>Bem-vindo(a), <?= htmlspecialchars(explode(' ', $u['name'])[0]) ?>!</h1>
            <p>Aqui você pode acompanhar seus agendamentos e informações da clínica.</p>
        </div>
        <?php if (is_admin()): ?>
        <a href="/Site-pet/pages/admin/index.php" class="btn btn-primary btn-sm">
            <i class="fas fa-arrow-left"></i> Voltar ao Painel Admin
        </a>
        <?php endif; ?>
    </div>

    <div class="painel-cards">
        <a href="/Site-pet/pages/consultas.php" class="service-card">
            <div class="service-icon"><i class="fas fa-calendar-check"></i></div>
            <h3>Agendar Consulta</h3>
            <p>Marque sua próxima consulta com a Dra. Milena.</p>
        </a>
        <a href="/Site-pet/pages/tosagem.php" class="service-card">
            <div class="service-icon"><i class="fas fa-scissors"></i></div>
            <h3>Agendar Banho & Tosa</h3>
            <p>Deixe seu pet sempre cheiroso e arrumado.</p>
        </a>
        <a href="/Site-pet/pages/loja.php" class="service-card">
            <div class="service-icon"><i class="fas fa-shopping-cart"></i></div>
            <h3>Loja Pet</h3>
            <p>Produtos para o seu animal de estimação.</p>
        </a>
        <a href="/Site-pet/pages/contato.php" class="service-card">
            <div class="service-icon"><i class="fas fa-envelope"></i></div>
            <h3>Falar com a Clínica</h3>
            <p>Tire dúvidas ou envie uma mensagem.</p>
        </a>
    </div>

    <!-- HORÁRIOS -->
    <?php
    $horarios = json_decode(file_get_contents(__DIR__ . '/../../data/horarios.json'), true);
    ?>
    <div class="schedule-card" style="margin-bottom:24px;">
        <div class="schedule-card-header">
            <i class="fas fa-clock"></i>
            <h3>Horários de Atendimento</h3>
        </div>
        <table class="schedule-table">
            <thead><tr><th>Dia</th><th>Consultas</th><th>Banho & Tosa</th></tr></thead>
            <tbody>
            <?php
            $cons = $horarios['consultas'];
            $tosa = $horarios['tosagem'];
            foreach ($cons as $i => $c):
                $t = $tosa[$i] ?? [];
                $cHor = $c['status']==='fechado' ? 'Fechado' : ($c['abertura'].' – '.$c['fechamento']);
                $tHor = ($t['status']??'')==='fechado' ? 'Fechado' : (($t['abertura']??'').' – '.($t['fechamento']??''));
            ?>
            <tr>
                <td><?= $c['dia'] ?></td>
                <td class="<?= $c['status']==='fechado'?'status-closed':($c['status']==='limitado'?'status-limit':'status-open') ?>"><?= $cHor ?></td>
                <td class="<?= ($t['status']??'')==='fechado'?'status-closed':(($t['status']??'')==='limitado'?'status-limit':'status-open') ?>"><?= $tHor ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div style="text-align:center;">
        <a href="https://wa.me/<?= CLINIC_WHATS ?>" target="_blank" class="btn btn-accent">
            <i class="fab fa-whatsapp"></i> Falar pelo WhatsApp – <?= CLINIC_PHONE ?>
        </a>
    </div>
</div>

<a href="https://wa.me/<?= CLINIC_WHATS ?>" target="_blank" class="whatsapp-float"><i class="fab fa-whatsapp"></i></a>
<script src="/Site-pet/assets/js/main.js"></script>
</body>
</html>
