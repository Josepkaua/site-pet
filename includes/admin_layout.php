<?php
// Recebe: $adminPage (aba ativa), $adminTitle, $adminSub
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/auth.php';
auth_admin();
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');

$u = auth_user();
$visitasHoje = load_json('visitas')['dias'][date('Y-m-d')] ?? 0;

$menu = [
    'Visão Geral' => [
        'dashboard'    => ['/pages/admin/index.php',        'fa-chart-line',     'Dashboard'],
        'visitas'      => ['/pages/admin/visitas.php',      'fa-eye',            'Visitas ao Site'],
    ],
    'Agendamentos' => [
        'agendamentos' => ['/pages/admin/agendamentos.php', 'fa-calendar-check', 'Consultas'],
        'tosagem'      => ['/pages/admin/tosagem_list.php', 'fa-scissors',       'Banho & Tosa'],
        'mensagens'    => ['/pages/admin/mensagens.php',    'fa-envelope',       'Mensagens'],
    ],
    'Configurações do Site' => [
        'horarios'     => ['/pages/admin/horarios.php',     'fa-clock',          'Horários'],
        'precos'       => ['/pages/admin/precos.php',       'fa-tags',           'Tabela de Preços'],
        'produtos'     => ['/pages/admin/produtos.php',     'fa-shopping-bag',   'Produtos da Loja'],
        'info'         => ['/pages/admin/info.php',         'fa-clinic-medical', 'Dados da Clínica'],
    ],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($adminTitle ?? 'Painel Admin') ?> – <?= e(CLINIC_NAME) ?></title>
    <link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/admin.css?v=2">
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay"></div>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-paw"></i>
        <div>
            <h2>Painel Admin</h2>
            <span><?= e(CLINIC_NAME) ?></span>
        </div>
    </div>

    <div class="sidebar-user">
        <div class="user-avatar"><?= e($u['avatar'] ?? '') ?></div>
        <div class="user-info">
            <strong><?= e($u['name'] ?? '') ?></strong>
            <span><?= e(($u['crmv'] ?? '') ?: 'Administradora') ?></span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <?php foreach ($menu as $section => $items): ?>
            <p class="nav-section-label"><?= e($section) ?></p>
            <?php foreach ($items as $key => [$href, $icon, $label]): ?>
            <a href="<?= $href ?>" class="sidebar-link <?= ($adminPage ?? '') === $key ? 'active' : '' ?>"><i class="fas <?= $icon ?>"></i> <?= e($label) ?></a>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="/index.php" target="_blank" rel="noopener"><i class="fas fa-external-link-alt"></i> Ver o Site</a>
        <a href="/pages/painel/index.php" target="_blank" rel="noopener"><i class="fas fa-user"></i> Ver Painel do Paciente</a>
        <a href="/pages/logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </div>
</aside>

<div class="admin-main">
    <header class="topbar">
        <button class="sidebar-toggle" id="sidebarToggle" type="button" aria-label="Abrir menu"><i class="fas fa-bars"></i></button>
        <div class="topbar-title">
            <h1><?= e($adminTitle ?? 'Dashboard') ?></h1>
            <p><?= e($adminSub ?? date('d/m/Y, H:i')) ?></p>
        </div>
        <div class="topbar-actions">
            <span class="topbar-visits"><i class="fas fa-eye"></i> <?= (int)$visitasHoje ?> visitas hoje</span>
            <a href="/index.php" target="_blank" rel="noopener" class="topbar-btn topbar-btn-outline">
                <i class="fas fa-external-link-alt"></i> <span>Ver Site</span>
            </a>
            <a href="/pages/logout.php" class="topbar-btn topbar-btn-outline">
                <i class="fas fa-sign-out-alt"></i> <span>Sair</span>
            </a>
        </div>
    </header>

    <div class="page-content">
    <?php if (!DB_CONFIGURED): ?>
        <div class="alert alert-error"><i class="fas fa-plug"></i>
            Banco de dados não conectado: defina a variável de ambiente <code>NHOST_ADMIN_SECRET</code> no servidor (Render → Environment).
        </div>
    <?php endif; ?>
    <?php if (admin_using_default_password()): ?>
        <div class="alert alert-warning"><i class="fas fa-shield-halved"></i>
            Você está usando a senha padrão de demonstração. Defina <code>ADMIN_PASSWORD_HASH</code> no servidor para proteger o painel (veja o README).
        </div>
    <?php endif; ?>
