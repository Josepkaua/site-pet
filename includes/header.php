<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? CLINIC_NAME ?></title>
    <link rel="stylesheet" href="/Site-pet/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<nav class="navbar">
    <div class="nav-container">
        <a href="/Site-pet/index.php" class="nav-logo">
            <i class="fas fa-paw"></i>
            <span><?= CLINIC_NAME ?></span>
        </a>
        <button class="nav-toggle" id="navToggle" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
        <ul class="nav-menu" id="navMenu">
            <li><a href="/Site-pet/index.php"           class="nav-link <?= ($activePage??'')==='home'      ? 'active':'' ?>"><i class="fas fa-home"></i> Início</a></li>
            <li><a href="/Site-pet/pages/servicos.php"  class="nav-link <?= ($activePage??'')==='servicos'  ? 'active':'' ?>"><i class="fas fa-stethoscope"></i> Serviços</a></li>
            <li><a href="/Site-pet/pages/consultas.php" class="nav-link <?= ($activePage??'')==='consultas' ? 'active':'' ?>"><i class="fas fa-calendar-check"></i> Consultas</a></li>
            <li><a href="/Site-pet/pages/loja.php"      class="nav-link <?= ($activePage??'')==='loja'      ? 'active':'' ?>"><i class="fas fa-shopping-cart"></i> Loja Pet</a></li>
            <li><a href="/Site-pet/pages/tosagem.php"   class="nav-link <?= ($activePage??'')==='tosagem'   ? 'active':'' ?>"><i class="fas fa-scissors"></i> Banho & Tosa</a></li>
            <li><a href="/Site-pet/pages/contato.php"   class="nav-link <?= ($activePage??'')==='contato'   ? 'active':'' ?>"><i class="fas fa-envelope"></i> Contato</a></li>
        </ul>
    </div>
</nav>
