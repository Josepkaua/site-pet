<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/tracker.php';
track_visit('Loja');
$pageTitle  = 'Loja Pet – ' . CLINIC_NAME;
$activePage = 'loja';

// Busca produtos ativos do Nhost
$q = gql('query { produtos(where:{ativo:{_eq:true}}, order_by:{categoria:asc,nome:asc}) { id nome descricao preco categoria emoji estoque } }');

if ($q['ok'] && !empty($q['data']['produtos'])) {
    $products = array_map(fn($p) => [
        'emoji'    => $p['emoji']    ?? '🐾',
        'name'     => $p['nome'],
        'desc'     => $p['descricao'] ?? '',
        'price'    => (float)$p['preco'],
        'category' => $p['categoria'] ?? '',
    ], $q['data']['produtos']);
} else {
    $products = [
        ['emoji'=>'🥩','name'=>'Racao Premium Cao Adulto 15kg','desc'=>'Alta digestibilidade, formula completa e balanceada.','price'=>189.90,'category'=>'racao'],
        ['emoji'=>'🐟','name'=>'Racao Premium Gato 3kg','desc'=>'Rico em omega-3, pelo brilhante e digestao saudavel.','price'=>79.90,'category'=>'racao'],
        ['emoji'=>'🦴','name'=>'Petisco Natural Cao 200g','desc'=>'Snack natural sem aditivos, excelente para treinamento.','price'=>24.90,'category'=>'petisco'],
        ['emoji'=>'🐠','name'=>'Petisco Gato Atum 60g','desc'=>'Lanche delicioso e nutritivo para felinos exigentes.','price'=>12.90,'category'=>'petisco'],
        ['emoji'=>'🪮','name'=>'Escova de Remocao de Pelos','desc'=>'Reduz a queda de pelos em ate 90%. Ergonomica.','price'=>54.90,'category'=>'acessorio'],
        ['emoji'=>'🎾','name'=>'Bola Interativa com Apito','desc'=>'Brinquedo resistente para caes de todos os portes.','price'=>34.90,'category'=>'brinquedo'],
        ['emoji'=>'🛁','name'=>'Shampoo Neutro Pet 500ml','desc'=>'Formula suave para banhos frequentes sem ressecar.','price'=>29.90,'category'=>'higiene'],
        ['emoji'=>'💊','name'=>'Vermifugo Comprimido (4cp)','desc'=>'Eficaz contra lombrigas, ancilostomos e outros.','price'=>38.90,'category'=>'saude'],
        ['emoji'=>'🏠','name'=>'Caminha Pet Tamanho M','desc'=>'Confortavel e lavavel, perfeita para caes e gatos.','price'=>119.90,'category'=>'acessorio'],
        ['emoji'=>'🧴','name'=>'Colonia Pet Floral 100ml','desc'=>'Fragrancia suave que dura ate 48h.','price'=>22.90,'category'=>'higiene'],
        ['emoji'=>'🎀','name'=>'Kit Lacinhos e Gravatas','desc'=>'Acessorios fashion para deixar seu pet ainda mais fofo.','price'=>19.90,'category'=>'acessorio'],
    ];
}

// Mapeamento de visual por categoria e palavras-chave no nome
function product_visual(string $name, string $cat, string $emoji): array {
    $n = mb_strtolower($name);

    // Icone + gradiente + cor de destaque
    $map = [
        // por palavra-chave no nome
        'racao'     => ['icon'=>'fa-bag-shopping',   'grad'=>'135deg,#f39c12,#e67e22', 'accent'=>'#f39c12', 'bg'=>'#fef5e7'],
        'petisco'   => ['icon'=>'fa-bone',            'grad'=>'135deg,#a0522d,#c0392b', 'accent'=>'#a0522d', 'bg'=>'#fdf0e8'],
        'escova'    => ['icon'=>'fa-soap',            'grad'=>'135deg,#8e44ad,#6c3483', 'accent'=>'#8e44ad', 'bg'=>'#f5eef8'],
        'shampoo'   => ['icon'=>'fa-pump-soap',       'grad'=>'135deg,#2980b9,#1a5276', 'accent'=>'#2980b9', 'bg'=>'#eaf4fb'],
        'vermifugo' => ['icon'=>'fa-pills',           'grad'=>'135deg,#27ae60,#1e8449', 'accent'=>'#27ae60', 'bg'=>'#e9f7ef'],
        'vermif'    => ['icon'=>'fa-pills',           'grad'=>'135deg,#27ae60,#1e8449', 'accent'=>'#27ae60', 'bg'=>'#e9f7ef'],
        'bola'      => ['icon'=>'fa-baseball',        'grad'=>'135deg,#e74c3c,#c0392b', 'accent'=>'#e74c3c', 'bg'=>'#fdedec'],
        'brinquedo' => ['icon'=>'fa-star',            'grad'=>'135deg,#f1c40f,#d4ac0d', 'accent'=>'#d4ac0d', 'bg'=>'#fefde7'],
        'caminha'   => ['icon'=>'fa-bed',             'grad'=>'135deg,#16a085,#0e6655', 'accent'=>'#16a085', 'bg'=>'#e8f8f5'],
        'colonia'   => ['icon'=>'fa-spray-can-sparkles','grad'=>'135deg,#9b59b6,#7d3c98','accent'=>'#9b59b6','bg'=>'#f5eef8'],
        'anti-pulg' => ['icon'=>'fa-syringe',         'grad'=>'135deg,#2ecc71,#27ae60', 'accent'=>'#2ecc71', 'bg'=>'#e9f7ef'],
        'pulgas'    => ['icon'=>'fa-syringe',         'grad'=>'135deg,#2ecc71,#27ae60', 'accent'=>'#2ecc71', 'bg'=>'#e9f7ef'],
        'pipeta'    => ['icon'=>'fa-syringe',         'grad'=>'135deg,#2ecc71,#27ae60', 'accent'=>'#2ecc71', 'bg'=>'#e9f7ef'],
        'lacinho'   => ['icon'=>'fa-ribbon',          'grad'=>'135deg,#e91e8c,#c2185b', 'accent'=>'#e91e8c', 'bg'=>'#fce4ec'],
        'gravata'   => ['icon'=>'fa-ribbon',          'grad'=>'135deg,#e91e8c,#c2185b', 'accent'=>'#e91e8c', 'bg'=>'#fce4ec'],
    ];

    // Por categoria como fallback
    $catMap = [
        'racao'     => ['icon'=>'fa-bag-shopping',   'grad'=>'135deg,#f39c12,#e67e22', 'accent'=>'#f39c12', 'bg'=>'#fef5e7'],
        'petisco'   => ['icon'=>'fa-bone',            'grad'=>'135deg,#a0522d,#cd5c5c', 'accent'=>'#a0522d', 'bg'=>'#fdf0e8'],
        'higiene'   => ['icon'=>'fa-pump-soap',       'grad'=>'135deg,#2980b9,#1a5276', 'accent'=>'#2980b9', 'bg'=>'#eaf4fb'],
        'saude'     => ['icon'=>'fa-pills',           'grad'=>'135deg,#27ae60,#1e8449', 'accent'=>'#27ae60', 'bg'=>'#e9f7ef'],
        'acessorio' => ['icon'=>'fa-paw',             'grad'=>'135deg,#8e44ad,#6c3483', 'accent'=>'#8e44ad', 'bg'=>'#f5eef8'],
        'brinquedo' => ['icon'=>'fa-star',            'grad'=>'135deg,#f1c40f,#d4ac0d', 'accent'=>'#d4ac0d', 'bg'=>'#fefde7'],
    ];

    foreach ($map as $keyword => $style) {
        if (str_contains($n, $keyword)) return $style;
    }
    return $catMap[$cat] ?? ['icon'=>'fa-paw','grad'=>'135deg,#2e9e6b,#1e7a50','accent'=>'#2e9e6b','bg'=>'#e8f8f0'];
}

$categories = ['Todos'=>'','Racao'=>'racao','Petiscos'=>'petisco','Higiene'=>'higiene','Saude'=>'saude','Acessorios'=>'acessorio','Brinquedos'=>'brinquedo'];
$filter   = $_GET['cat'] ?? '';
$filtered = $filter ? array_filter($products, fn($p) => $p['category'] === $filter) : $products;
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

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

<section class="page-hero">
    <h1><i class="fas fa-shopping-cart"></i> Loja Pet</h1>
    <p>Racoes, petiscos, acessorios, higiene e saude – tudo para o seu animal de estimacao.</p>
</section>

<section class="section">
    <div class="container">

        <!-- FILTROS + BOTAO CARRINHO -->
        <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:36px;align-items:center;">
            <?php foreach ($categories as $label => $val): ?>
            <a href="?cat=<?= $val ?>"
               class="btn <?= $filter === $val ? 'btn-primary' : 'btn-outline' ?> btn-sm">
                <?= $label ?>
            </a>
            <?php endforeach; ?>
            <button class="btn btn-accent btn-sm" id="cartToggle" style="margin-left:auto;">
                <i class="fas fa-shopping-cart"></i>
                <span id="cartCount" style="display:none;background:#fff;color:var(--accent);border-radius:50px;padding:0 6px;font-size:.72rem;font-weight:700;">0</span>
                Carrinho
            </button>
        </div>

        <?php if (empty($filtered)): ?>
        <p style="text-align:center;color:var(--text-light);padding:48px 0;">Nenhum produto encontrado nesta categoria.</p>
        <?php else: ?>

        <!-- GRID DE PRODUTOS -->
        <div class="products-grid">
            <?php foreach ($filtered as $p):
                $vis = product_visual($p['name'], $p['category'], $p['emoji']);
            ?>
            <div class="product-card">

                <!-- VISUAL DO PRODUTO -->
                <div class="product-visual" style="background:<?= $vis['bg'] ?>;">
                    <div class="product-visual-inner" style="background:linear-gradient(<?= $vis['grad'] ?>);">
                        <i class="fas <?= $vis['icon'] ?>"></i>
                    </div>
                    <div class="product-visual-emoji"><?= $p['emoji'] ?></div>
                    <div class="product-visual-shine"></div>
                </div>

                <div class="product-info">
                    <span class="product-cat-tag" style="background:<?= $vis['bg'] ?>;color:<?= $vis['accent'] ?>;">
                        <?= ucfirst($p['category'] ?: 'Produto') ?>
                    </span>
                    <h3><?= htmlspecialchars($p['name']) ?></h3>
                    <p><?= htmlspecialchars($p['desc']) ?></p>
                    <div class="product-footer">
                        <span class="product-price" style="color:<?= $vis['accent'] ?>;">
                            R$ <?= number_format($p['price'], 2, ',', '.') ?>
                        </span>
                        <button class="btn btn-sm btn-comprar"
                                style="background:linear-gradient(<?= $vis['grad'] ?>);color:#fff;border:none;box-shadow:0 4px 14px <?= $vis['accent'] ?>55;"
                                data-product="<?= htmlspecialchars($p['name']) ?>"
                                data-price="<?= $p['price'] ?>"
                                data-emoji="<?= htmlspecialchars($p['emoji']) ?>">
                            <i class="fas fa-cart-plus"></i> Comprar
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- INFO FRETE -->
        <div class="loja-info-bar">
            <div class="loja-info-item">
                <div class="loja-info-icon" style="background:linear-gradient(135deg,#2e9e6b,#1e7a50);">
                    <i class="fas fa-truck"></i>
                </div>
                <div><strong>Entrega local</strong><small>Na cidade e regiao</small></div>
            </div>
            <div class="loja-info-item">
                <div class="loja-info-icon" style="background:linear-gradient(135deg,#2980b9,#1a5276);">
                    <i class="fas fa-store"></i>
                </div>
                <div><strong>Retire na loja</strong><small>Gratis e rapido</small></div>
            </div>
            <div class="loja-info-item">
                <div class="loja-info-icon" style="background:linear-gradient(135deg,#8e44ad,#6c3483);">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div><strong>Parcelamos</strong><small>Em ate 6x sem juros</small></div>
            </div>
            <div class="loja-info-item">
                <div class="loja-info-icon" style="background:linear-gradient(135deg,#25d366,#128C7E);">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <div><strong>Peca pelo WhatsApp</strong><small><?= CLINIC_PHONE ?></small></div>
            </div>
        </div>

    </div>
</section>

<a href="https://wa.me/<?= CLINIC_WHATS ?>" target="_blank" class="whatsapp-float">
    <i class="fab fa-whatsapp"></i>
</a>

<script>const WHATS_NUMBER = '<?= CLINIC_WHATS ?>';</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
