<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/tracker.php';
track_visit('Inicio');
$logado   = is_logged();
$pageTitle = CLINIC_NAME . ' - Saude e Amor para o seu Pet';

// Produtos em destaque: busca do Nhost (somente ativos), fallback estatico
$qDest = gql('query { produtos(where:{ativo:{_eq:true}}, order_by:{id:asc}, limit:6) { nome preco emoji categoria } }');
if ($qDest['ok'] && !empty($qDest['data']['produtos'])) {
    $destaques = $qDest['data']['produtos'];
} else {
    $destaques = [
        ['emoji'=>'🥩','nome'=>'Racao Premium Cao 15kg',    'preco'=>189.90,'categoria'=>'racao'],
        ['emoji'=>'🦴','nome'=>'Petisco Natural Cao 200g',  'preco'=>24.90, 'categoria'=>'petisco'],
        ['emoji'=>'🪮','nome'=>'Escova de Remocao de Pelos','preco'=>54.90, 'categoria'=>'acessorio'],
        ['emoji'=>'🛁','nome'=>'Shampoo Neutro Pet 500ml',  'preco'=>29.90, 'categoria'=>'higiene'],
        ['emoji'=>'🎾','nome'=>'Bola Interativa com Apito', 'preco'=>34.90, 'categoria'=>'brinquedo'],
        ['emoji'=>'💊','nome'=>'Vermifugo Comprimido (4cp)','preco'=>38.90, 'categoria'=>'saude'],
    ];
}

function dest_visual(string $nome, string $cat): array {
    $n   = mb_strtolower($nome);
    $map = [
        'racao'     => ['icon'=>'fa-bag-shopping',    'grad'=>'135deg,#f39c12,#e67e22','accent'=>'#e67e22','bg'=>'#fef5e7'],
        'petisco'   => ['icon'=>'fa-bone',             'grad'=>'135deg,#a0522d,#c0392b','accent'=>'#a0522d','bg'=>'#fdf0e8'],
        'escova'    => ['icon'=>'fa-soap',             'grad'=>'135deg,#8e44ad,#6c3483','accent'=>'#8e44ad','bg'=>'#f5eef8'],
        'shampoo'   => ['icon'=>'fa-pump-soap',        'grad'=>'135deg,#2980b9,#1a5276','accent'=>'#2980b9','bg'=>'#eaf4fb'],
        'vermifugo' => ['icon'=>'fa-pills',            'grad'=>'135deg,#27ae60,#1e8449','accent'=>'#27ae60','bg'=>'#e9f7ef'],
        'vermif'    => ['icon'=>'fa-pills',            'grad'=>'135deg,#27ae60,#1e8449','accent'=>'#27ae60','bg'=>'#e9f7ef'],
        'bola'      => ['icon'=>'fa-baseball',         'grad'=>'135deg,#e74c3c,#c0392b','accent'=>'#e74c3c','bg'=>'#fdedec'],
        'caminha'   => ['icon'=>'fa-bed',              'grad'=>'135deg,#16a085,#0e6655','accent'=>'#16a085','bg'=>'#e8f8f5'],
        'colonia'   => ['icon'=>'fa-spray-can-sparkles','grad'=>'135deg,#9b59b6,#7d3c98','accent'=>'#9b59b6','bg'=>'#f5eef8'],
        'pulgas'    => ['icon'=>'fa-syringe',          'grad'=>'135deg,#2ecc71,#27ae60','accent'=>'#27ae60','bg'=>'#e9f7ef'],
        'pipeta'    => ['icon'=>'fa-syringe',          'grad'=>'135deg,#2ecc71,#27ae60','accent'=>'#27ae60','bg'=>'#e9f7ef'],
        'lacinho'   => ['icon'=>'fa-ribbon',           'grad'=>'135deg,#e91e8c,#c2185b','accent'=>'#e91e8c','bg'=>'#fce4ec'],
        'gravata'   => ['icon'=>'fa-ribbon',           'grad'=>'135deg,#e91e8c,#c2185b','accent'=>'#e91e8c','bg'=>'#fce4ec'],
    ];
    $catMap = [
        'racao'     => ['icon'=>'fa-bag-shopping',    'grad'=>'135deg,#f39c12,#e67e22','accent'=>'#e67e22','bg'=>'#fef5e7'],
        'petisco'   => ['icon'=>'fa-bone',             'grad'=>'135deg,#a0522d,#cd5c5c','accent'=>'#a0522d','bg'=>'#fdf0e8'],
        'higiene'   => ['icon'=>'fa-pump-soap',        'grad'=>'135deg,#2980b9,#1a5276','accent'=>'#2980b9','bg'=>'#eaf4fb'],
        'saude'     => ['icon'=>'fa-pills',            'grad'=>'135deg,#27ae60,#1e8449','accent'=>'#27ae60','bg'=>'#e9f7ef'],
        'acessorio' => ['icon'=>'fa-paw',              'grad'=>'135deg,#8e44ad,#6c3483','accent'=>'#8e44ad','bg'=>'#f5eef8'],
        'brinquedo' => ['icon'=>'fa-star',             'grad'=>'135deg,#f1c40f,#d4ac0d','accent'=>'#d4ac0d','bg'=>'#fefde7'],
    ];
    foreach ($map as $kw => $v) { if (str_contains($n, $kw)) return $v; }
    return $catMap[$cat] ?? ['icon'=>'fa-paw','grad'=>'135deg,#2e9e6b,#1e7a50','accent'=>'#2e9e6b','bg'=>'#e8f8f0'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="/Site-pet/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="index-page">

<!-- CARRINHO SIDEBAR -->
<div class="cart-overlay" id="cartOverlay"></div>
<aside class="cart-sidebar" id="cartSidebar">
    <div class="cart-sidebar-header">
        <h3><i class="fas fa-shopping-cart"></i> Meu Carrinho</h3>
        <button class="cart-close" id="cartClose"><i class="fas fa-times"></i></button>
    </div>
    <div class="cart-items" id="cartItems">
        <div class="cart-empty" id="cartEmpty">
            <i class="fas fa-shopping-basket"></i>
            <p>Seu carrinho esta vazio</p>
            <small>Adicione produtos para comecar</small>
        </div>
    </div>
    <div class="cart-footer" id="cartFooter" style="display:none;">
        <div class="cart-total-row">
            <span>Total</span>
            <strong id="cartTotal">R$ 0,00</strong>
        </div>
        <button class="btn btn-primary" style="width:100%;justify-content:center;" id="cartCheckout">
            <i class="fab fa-whatsapp"></i> Finalizar pelo WhatsApp
        </button>
        <button class="btn btn-outline" style="width:100%;justify-content:center;margin-top:8px;" id="cartClear">
            <i class="fas fa-trash"></i> Limpar carrinho
        </button>
    </div>
</aside>

<!-- NAVBAR -->
<nav class="navbar-index">
    <div class="nav-container">
        <div class="nav-logo">
            <i class="fas fa-paw"></i>
            <span><?= CLINIC_NAME ?></span>
        </div>
        <div style="display:flex;gap:10px;align-items:center;">

            <button class="cart-btn" id="cartToggle" title="Abrir carrinho">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-count" id="cartCount" style="display:none;">0</span>
            </button>

            <a href="<?= $logado ? (is_admin() ? '/Site-pet/pages/admin/index.php' : '/Site-pet/pages/painel/index.php') : '/Site-pet/pages/login.php' ?>"
               class="btn btn-outline btn-sm">
                <i class="fas fa-<?= $logado ? 'user-circle' : 'sign-in-alt' ?>"></i>
                Login
            </a>

            <?php if ($logado): ?>
            <a href="/Site-pet/pages/servicos.php" class="btn btn-primary btn-sm">
                <i class="fas fa-paw"></i> Ver Servicos
            </a>
            <?php else: ?>
            <a href="/Site-pet/pages/login.php" class="btn btn-primary btn-sm btn-lock"
               title="Faca login para ver os servicos">
                <i class="fas fa-lock"></i> Ver Servicos
            </a>
            <?php endif; ?>

        </div>
    </div>
</nav>

<!-- HERO -->
<section class="hero hero-index">
    <div class="hero-container">
        <div class="hero-content">
            <div class="hero-badges">
                <span class="badge"><i class="fas fa-award"></i> <?= CLINIC_CRMV ?></span>
                <span class="badge"><i class="fas fa-star"></i> +500 Pacientes</span>
                <span class="badge"><i class="fas fa-heart"></i> 10 anos de experiencia</span>
            </div>
            <h1>Cuidando do seu <span>Pet</span><br>com Amor e Dedicacao</h1>
            <p>Consultas veterinarias, banho &amp; tosa, vacinacao, exames e muito mais. O seu animal merece o melhor cuidado!</p>
            <div class="hero-buttons">
                <?php if ($logado): ?>
                <a href="/Site-pet/pages/servicos.php" class="btn btn-primary">
                    <i class="fas fa-stethoscope"></i> Ver Servicos
                </a>
                <?php else: ?>
                <a href="/Site-pet/pages/login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Fazer Login
                </a>
                <?php endif; ?>
                <a href="https://wa.me/<?= CLINIC_WHATS ?>" target="_blank" class="btn btn-outline">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </a>
            </div>
        </div>
        <div class="hero-image">
            <img src="/Site-pet/assets/images/dra-vet.png" alt="<?= CLINIC_DRA ?>">
        </div>
    </div>
</section>

<!-- SOBRE A DRA -->
<section class="section section-alt">
    <div class="container">
        <div class="about-section">
            <div class="about-image">
                <img src="/Site-pet/assets/images/dra-vet.png" alt="<?= CLINIC_DRA ?>">
                <div class="about-badge-float">
                    <i class="fas fa-award"></i>
                    <div>
                        <strong>10+</strong>
                        <span>Anos de experiencia</span>
                    </div>
                </div>
            </div>
            <div class="about-content">
                <span class="section-tag">Quem somos</span>
                <h2>Conheca <?= CLINIC_DRA ?></h2>
                <p class="dra-title"><i class="fas fa-user-md"></i> Medica Veterinaria – <?= CLINIC_CRMV ?></p>
                <p>Com mais de 10 anos de experiencia no atendimento de pequenos animais, <?= CLINIC_DRA ?> e apaixonada pelo que faz.</p>
                <p>Acredita que cada animal merece atencao individualizada, diagnostico preciso e tratamento humanizado.</p>
                <div class="about-creds">
                    <div class="cred-item"><div class="cred-item i"><i class="fas fa-graduation-cap"></i></div><span>Graduacao em Medicina Veterinaria</span></div>
                    <div class="cred-item"><div class="cred-item i"><i class="fas fa-certificate"></i></div><span>Especializacao em Clinica e Cirurgia de Pequenos Animais</span></div>
                    <div class="cred-item"><div class="cred-item i"><i class="fas fa-heart"></i></div><span>Membro da Associacao Brasileira de Medicina Veterinaria</span></div>
                    <div class="cred-item"><div class="cred-item i"><i class="fas fa-microscope"></i></div><span>Atualizacao continua em Dermatologia e Cardiologia Pet</span></div>
                </div>
                <br>
                <?php if ($logado): ?>
                <a href="/Site-pet/pages/servicos.php" class="btn btn-primary">
                    <i class="fas fa-arrow-right"></i> Conhecer a Clinica
                </a>
                <?php else: ?>
                <a href="/Site-pet/pages/login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Fazer Login para Ver
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- PRODUTOS EM DESTAQUE -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Loja Pet</span>
            <h2>Produtos em Destaque</h2>
            <p>Os favoritos da nossa loja — qualidade garantida para o seu pet</p>
        </div>
        <div class="dest-grid">
            <?php foreach ($destaques as $p):
                $vis = dest_visual($p['nome'], $p['categoria'] ?? '');
            ?>
            <div class="dest-card">
                <!-- Imagem ilustrativa -->
                <div class="dest-img" style="background:<?= $vis['bg'] ?>;">
                    <div class="dest-img-circle" style="background:linear-gradient(<?= $vis['grad'] ?>);">
                        <i class="fas <?= $vis['icon'] ?>"></i>
                    </div>
                    <span class="dest-img-emoji"><?= $p['emoji'] ?></span>
                    <div class="dest-img-shine"></div>
                </div>

                <!-- Conteudo -->
                <div class="dest-body">
                    <h4><?= htmlspecialchars($p['nome']) ?></h4>
                    <div class="dest-footer">
                        <span class="dest-price" style="color:<?= $vis['accent'] ?>;">
                            R$ <?= number_format((float)$p['preco'], 2, ',', '.') ?>
                        </span>
                        <button class="btn btn-sm btn-comprar"
                                style="background:linear-gradient(<?= $vis['grad'] ?>);color:#fff;box-shadow:0 4px 14px <?= $vis['accent'] ?>44;"
                                data-product="<?= htmlspecialchars($p['nome']) ?>"
                                data-price="<?= $p['preco'] ?>"
                                data-emoji="<?= htmlspecialchars($p['emoji']) ?>">
                            <i class="fas fa-cart-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align:center;margin-top:36px;">
            <a href="/Site-pet/pages/loja.php" class="btn btn-outline">
                <i class="fas fa-shopping-bag"></i> Ver toda a loja
            </a>
        </div>
    </div>
</section>

<!-- RODAPE -->
<footer class="footer-index">
    <p>
        <i class="fas fa-map-marker-alt"></i> <?= CLINIC_ADDRESS ?>
        &nbsp;|&nbsp;
        <i class="fas fa-phone"></i> <?= CLINIC_PHONE ?>
        &nbsp;|&nbsp;
        &copy; <?= date('Y') ?> <?= CLINIC_NAME ?>
    </p>
</footer>

<a href="https://wa.me/<?= CLINIC_WHATS ?>" target="_blank" class="whatsapp-float" title="WhatsApp">
    <i class="fab fa-whatsapp"></i>
</a>

<script>const WHATS_NUMBER = '<?= CLINIC_WHATS ?>';</script>
<script src="/Site-pet/assets/js/main.js"></script>
</body>
</html>
