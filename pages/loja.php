<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/tracker.php';
track_visit('Loja');
$pageTitle  = 'Loja Pet – ' . CLINIC_NAME;
$activePage = 'loja';

$products = produtos_ativos();
$filter   = pick($_GET['cat'] ?? '', array_keys(CATEGORIAS));
$busca    = clean_text($_GET['q'] ?? '', 60);
$filtered = array_filter($products, function ($p) use ($filter, $busca) {
    if ($filter && ($p['categoria'] ?? '') !== $filter) return false;
    if ($busca !== '' && mb_stripos($p['nome'] . ' ' . ($p['descricao'] ?? ''), $busca) === false) return false;
    return true;
});

require __DIR__ . '/../includes/header.php';
?>

<section class="page-hero">
    <h1 class="anim-up"><i class="fas fa-bag-shopping"></i> Loja Pet</h1>
    <p class="anim-up" style="--d:.1s">Rações, petiscos, acessórios, higiene e saúde – tudo para o seu animal de estimação.</p>
</section>

<section class="section">
    <div class="container">

        <div class="shop-toolbar reveal">
            <nav class="chip-row" aria-label="Categorias">
                <a href="/pages/loja.php" class="chip <?= $filter === '' ? 'active' : '' ?>">Todos</a>
                <?php foreach (CATEGORIAS as $val => $label): ?>
                <a href="?cat=<?= $val ?>" class="chip <?= $filter === $val ? 'active' : '' ?>"><?= e($label) ?></a>
                <?php endforeach; ?>
            </nav>
            <form class="shop-search" method="GET" role="search">
                <?php if ($filter): ?><input type="hidden" name="cat" value="<?= e($filter) ?>"><?php endif; ?>
                <i class="fas fa-magnifying-glass"></i>
                <input type="search" name="q" value="<?= e($busca) ?>" placeholder="Buscar produto..." aria-label="Buscar produto">
            </form>
        </div>

        <?php if (empty($filtered)): ?>
        <div class="empty-state reveal">
            <i class="fas fa-box-open"></i>
            <p>Nenhum produto encontrado<?= $busca ? ' para “' . e($busca) . '”' : ' nesta categoria' ?>.</p>
            <a href="/pages/loja.php" class="btn btn-outline btn-sm">Ver todos os produtos</a>
        </div>
        <?php else: ?>

        <div class="products-grid" data-reveal-group>
            <?php foreach ($filtered as $p):
                $vis = product_visual($p['nome'], $p['categoria'] ?? '');
                $semEstoque = isset($p['estoque']) && (int)$p['estoque'] <= 0;
            ?>
            <article class="product-card reveal <?= $semEstoque ? 'is-out' : '' ?>"
                     style="--accent:<?= e($vis['accent']) ?>;--grad:linear-gradient(<?= e($vis['grad']) ?>);--soft:<?= e($vis['bg']) ?>">
                <div class="product-visual">
                    <div class="product-visual-inner"><i class="fas <?= e($vis['icon']) ?>"></i></div>
                    <div class="product-visual-emoji" aria-hidden="true"><?= e($p['emoji'] ?? '🐾') ?></div>
                    <div class="product-visual-shine"></div>
                    <?php if ($semEstoque): ?><span class="product-flag">Esgotado</span>
                    <?php elseif (isset($p['estoque']) && (int)$p['estoque'] <= 5): ?><span class="product-flag flag-low">Últimas unidades</span><?php endif; ?>
                </div>
                <div class="product-info">
                    <span class="product-cat-tag"><?= e(CATEGORIAS[$p['categoria'] ?? ''] ?? 'Produto') ?></span>
                    <h3><?= e($p['nome']) ?></h3>
                    <p><?= e($p['descricao'] ?? '') ?></p>
                    <div class="product-footer">
                        <span class="product-price"><?= brl($p['preco']) ?></span>
                        <button class="btn btn-sm btn-comprar" type="button" <?= $semEstoque ? 'disabled' : '' ?>
                                data-product="<?= e($p['nome']) ?>" data-price="<?= (float)$p['preco'] ?>" data-emoji="<?= e($p['emoji'] ?? '🐾') ?>">
                            <i class="fas fa-cart-plus"></i> <?= $semEstoque ? 'Indisponível' : 'Comprar' ?>
                        </button>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="loja-info-bar" data-reveal-group>
            <div class="loja-info-item reveal"><div class="loja-info-icon" style="--grad:linear-gradient(135deg,#2e9e6b,#1e7a50)"><i class="fas fa-truck"></i></div>
                <div><strong>Entrega local</strong><small>Na cidade e região</small></div></div>
            <div class="loja-info-item reveal"><div class="loja-info-icon" style="--grad:linear-gradient(135deg,#2980b9,#1a5276)"><i class="fas fa-store"></i></div>
                <div><strong>Retire na loja</strong><small>Grátis e rápido</small></div></div>
            <div class="loja-info-item reveal"><div class="loja-info-icon" style="--grad:linear-gradient(135deg,#8e44ad,#6c3483)"><i class="fas fa-credit-card"></i></div>
                <div><strong>Parcelamos</strong><small>Em até 6x sem juros</small></div></div>
            <div class="loja-info-item reveal"><div class="loja-info-icon" style="--grad:linear-gradient(135deg,#25d366,#128C7E)"><i class="fab fa-whatsapp"></i></div>
                <div><strong>Peça pelo WhatsApp</strong><small><?= e(CLINIC_PHONE) ?></small></div></div>
        </div>

    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
