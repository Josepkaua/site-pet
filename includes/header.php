<?php
/**
 * Cabeçalho público. Variáveis opcionais antes do include:
 *   $pageTitle, $pageDescription, $activePage, $bodyClass
 */
require_once __DIR__ . '/auth.php';

$pageTitle       = $pageTitle       ?? CLINIC_NAME;
$pageDescription = $pageDescription ?? 'Clínica veterinária com consultas, vacinas, exames, banho & tosa e loja pet. ' . CLINIC_DRA . ' – ' . CLINIC_CRMV . '.';
$activePage      = $activePage      ?? '';
$navLinks = [
    'home'      => ['/index.php',           'fa-house',          'Início'],
    'servicos'  => ['/pages/servicos.php',  'fa-stethoscope',    'Serviços'],
    'consultas' => ['/pages/consultas.php', 'fa-calendar-check', 'Consultas'],
    'tosagem'   => ['/pages/tosagem.php',   'fa-scissors',       'Banho & Tosa'],
    'loja'      => ['/pages/loja.php',      'fa-bag-shopping',   'Loja Pet'],
    'contato'   => ['/pages/contato.php',   'fa-envelope',       'Contato'],
];
$scheme  = (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https' || !empty($_SERVER['HTTPS'])) ? 'https' : 'http';
$baseUrl = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <meta name="theme-color" content="#2e9e6b">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDescription) ?>">
    <meta property="og:image" content="<?= e($baseUrl) ?>/assets/images/og-image.jpg">
    <meta property="og:locale" content="pt_BR">
    <link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css?v=3">
    <script>document.documentElement.classList.add('js-reveal');</script>
</head>
<body class="<?= e($bodyClass ?? '') ?>">
<a class="skip-link" href="#conteudo">Pular para o conteúdo</a>

<nav class="navbar" id="navbar">
    <div class="nav-container">
        <a href="/index.php" class="nav-logo" aria-label="<?= e(CLINIC_NAME) ?> – início">
            <span class="nav-logo-icon"><i class="fas fa-paw"></i></span>
            <span><?= e(CLINIC_NAME) ?></span>
        </a>

        <ul class="nav-menu" id="navMenu">
            <?php foreach ($navLinks as $key => [$href, $icon, $label]): ?>
            <li><a href="<?= $href ?>" class="nav-link <?= $activePage === $key ? 'active' : '' ?>"
                   <?= $activePage === $key ? 'aria-current="page"' : '' ?>>
                <i class="fas <?= $icon ?>"></i> <?= e($label) ?></a></li>
            <?php endforeach; ?>
        </ul>

        <div class="nav-actions">
            <button class="cart-btn" id="cartToggle" type="button" aria-label="Abrir carrinho">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-count" id="cartCount" hidden>0</span>
            </button>
            <?php if (is_logged()): ?>
            <a href="<?= is_admin() ? '/pages/admin/index.php' : '/pages/painel/index.php' ?>" class="btn btn-outline btn-sm nav-account">
                <i class="fas fa-user-circle"></i> <span><?= is_admin() ? 'Painel' : 'Minha conta' ?></span>
            </a>
            <?php else: ?>
            <a href="/pages/login.php" class="btn btn-outline btn-sm nav-account">
                <i class="fas fa-sign-in-alt"></i> <span>Entrar</span>
            </a>
            <?php endif; ?>
            <button class="nav-toggle" id="navToggle" type="button" aria-label="Abrir menu" aria-expanded="false" aria-controls="navMenu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</nav>

<?php require __DIR__ . '/partials/cart.php'; ?>

<main id="conteudo">
