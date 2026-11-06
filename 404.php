<?php
require_once __DIR__ . '/includes/bootstrap.php';
http_response_code(404);
$pageTitle = 'Página não encontrada – ' . CLINIC_NAME;
require __DIR__ . '/includes/header.php';
?>
<section class="section not-found">
    <div class="container">
        <div class="nf-paws" aria-hidden="true"><i class="fas fa-paw"></i><i class="fas fa-paw"></i><i class="fas fa-paw"></i></div>
        <h1 class="anim-up">404</h1>
        <p class="anim-up" style="--d:.1s">Ops! Essa página saiu para passear e ainda não voltou. 🐶</p>
        <div class="hero-buttons anim-up" style="--d:.2s">
            <a href="/index.php" class="btn btn-primary"><i class="fas fa-house"></i> Voltar ao início</a>
            <a href="/pages/contato.php" class="btn btn-outline"><i class="fas fa-envelope"></i> Fale conosco</a>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
