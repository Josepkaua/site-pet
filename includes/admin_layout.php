<?php
// Recebe: $adminPage (string com a aba ativa), $adminTitle, $adminSub
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';
auth_admin();

$u = auth_user();

// Contagens para badges (lê de arquivos JSON já que Nhost pode não estar configurado)
$dataDir  = __DIR__ . '/../data/';
$visitas  = json_decode(@file_get_contents($dataDir . 'visitas.json'), true) ?? [];
$visitasHoje = $visitas['dias'][date('Y-m-d')] ?? 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $adminTitle ?? 'Painel Admin' ?> – <?= CLINIC_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/Site-pet/assets/css/admin.css">
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-paw"></i>
        <div>
            <h2>Painel Admin</h2>
            <span><?= CLINIC_NAME ?></span>
        </div>
    </div>

    <div class="sidebar-user">
        <div class="user-avatar"><?= htmlspecialchars($u['avatar']) ?></div>
        <div class="user-info">
            <strong><?= htmlspecialchars($u['name']) ?></strong>
            <span><?= htmlspecialchars($u['crmv'] ?: 'Administradora') ?></span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <p class="nav-section-label">Visão Geral</p>
        <a href="/Site-pet/pages/admin/index.php"         class="sidebar-link <?= $adminPage==='dashboard'    ? 'active':'' ?>"><i class="fas fa-chart-line"></i> Dashboard</a>
        <a href="/Site-pet/pages/admin/visitas.php"       class="sidebar-link <?= $adminPage==='visitas'      ? 'active':'' ?>"><i class="fas fa-eye"></i> Visitas ao Site</a>

        <p class="nav-section-label">Agendamentos</p>
        <a href="/Site-pet/pages/admin/agendamentos.php"  class="sidebar-link <?= $adminPage==='agendamentos' ? 'active':'' ?>"><i class="fas fa-calendar-check"></i> Consultas</a>
        <a href="/Site-pet/pages/admin/tosagem_list.php"  class="sidebar-link <?= $adminPage==='tosagem'      ? 'active':'' ?>"><i class="fas fa-scissors"></i> Banho & Tosa</a>
        <a href="/Site-pet/pages/admin/mensagens.php"     class="sidebar-link <?= $adminPage==='mensagens'    ? 'active':'' ?>"><i class="fas fa-envelope"></i> Mensagens</a>

        <p class="nav-section-label">Configurações do Site</p>
        <a href="/Site-pet/pages/admin/horarios.php"      class="sidebar-link <?= $adminPage==='horarios'     ? 'active':'' ?>"><i class="fas fa-clock"></i> Horários</a>
        <a href="/Site-pet/pages/admin/precos.php"        class="sidebar-link <?= $adminPage==='precos'       ? 'active':'' ?>"><i class="fas fa-tags"></i> Tabela de Preços</a>
        <a href="/Site-pet/pages/admin/produtos.php"      class="sidebar-link <?= $adminPage==='produtos'     ? 'active':'' ?>"><i class="fas fa-shopping-bag"></i> Produtos da Loja</a>
        <a href="/Site-pet/pages/admin/info.php"          class="sidebar-link <?= $adminPage==='info'         ? 'active':'' ?>"><i class="fas fa-clinic-medical"></i> Dados da Clínica</a>
    </nav>

    <div class="sidebar-footer">
        <a href="/Site-pet/index.php" target="_blank"><i class="fas fa-external-link-alt"></i> Ver o Site</a>
        <br><br>
        <a href="/Site-pet/pages/painel/index.php" target="_blank"><i class="fas fa-user"></i> Ver Painel do Paciente</a>
        <br><br>
        <a href="/Site-pet/pages/logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </div>
</aside>

<!-- MAIN -->
<div class="admin-main">
    <header class="topbar">
        <div class="topbar-title">
            <h1><?= $adminTitle ?? 'Dashboard' ?></h1>
            <p><?= $adminSub ?? date('d/m/Y, H:i') ?></p>
        </div>
        <div class="topbar-actions">
            <span style="font-size:.8rem;color:var(--text-light)"><i class="fas fa-eye"></i> <?= $visitasHoje ?> visitas hoje</span>
            <a href="/Site-pet/index.php" target="_blank" class="topbar-btn topbar-btn-outline">
                <i class="fas fa-external-link-alt"></i> Ver Site
            </a>
            <a href="/Site-pet/pages/logout.php" class="topbar-btn topbar-btn-outline">
                <i class="fas fa-sign-out-alt"></i> Sair
            </a>
        </div>
    </header>

    <div class="page-content">
