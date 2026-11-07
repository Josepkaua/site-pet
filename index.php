<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/tracker.php';
track_visit('Início');

$pageTitle  = CLINIC_NAME . ' – Saúde e Amor para o seu Pet';
$activePage = 'home';
$bodyClass  = 'index-page';
$destaques  = produtos_ativos(6);
$aberto     = clinica_aberta_agora();

$atalhos = [
    ['/pages/consultas.php', 'fa-stethoscope',  'Consultas',      'Clínica geral, retornos e atendimento domiciliar.'],
    ['/pages/servicos.php',  'fa-syringe',      'Vacinas',        'Calendário vacinal completo para cães e gatos.'],
    ['/pages/servicos.php',  'fa-flask',        'Exames',         'Hemograma, bioquímica, ultrassom e ECG.'],
    ['/pages/tosagem.php',   'fa-scissors',     'Banho & Tosa',   'Seu pet limpinho, cheiroso e feliz.'],
];

require __DIR__ . '/includes/header.php';
?>

<!-- HERO -->
<section class="hero hero-index">
    <div class="hero-paws" aria-hidden="true">
        <i class="fas fa-paw"></i><i class="fas fa-paw"></i><i class="fas fa-paw"></i><i class="fas fa-paw"></i><i class="fas fa-paw"></i>
    </div>
    <div class="hero-container">
        <div class="hero-content">
            <div class="hero-badges anim-up" style="--d:.05s">
                <span class="badge badge-status <?= $aberto ? 'is-open' : 'is-closed' ?>">
                    <span class="dot"></span> <?= $aberto ? 'Aberto agora' : 'Fechado agora' ?>
                </span>
                <span class="badge"><i class="fas fa-award"></i> <?= e(CLINIC_CRMV) ?></span>
            </div>
            <h1 class="anim-up" style="--d:.15s">Cuidando do seu <span class="text-highlight">Pet</span><br>com Amor e Dedicação</h1>
            <p class="anim-up" style="--d:.25s">Consultas veterinárias, banho &amp; tosa, vacinação, exames e muito mais. O seu animal merece o melhor cuidado!</p>
            <div class="hero-buttons anim-up" style="--d:.35s">
                <a href="/pages/consultas.php" class="btn btn-primary btn-shine">
                    <i class="fas fa-calendar-check"></i> Agendar Consulta
                </a>
                <a href="<?= e(whats_link('Olá! Gostaria de agendar um atendimento.')) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </a>
            </div>
            <div class="hero-stats anim-up" style="--d:.45s">
                <div><strong data-count="500" data-prefix="+">+500</strong><span>Pacientes atendidos</span></div>
                <div><strong data-count="10" data-suffix="+">10+</strong><span>Anos de experiência</span></div>
                <div><strong data-count="7" data-suffix="d">7d</strong><span>Urgência na semana</span></div>
            </div>
        </div>
        <div class="hero-image anim-zoom" style="--d:.2s">
            <picture>
                <source type="image/webp" srcset="/assets/images/dra-vet-480.webp 480w, /assets/images/dra-vet-800.webp 800w" sizes="(max-width: 900px) 280px, 420px">
                <img src="/assets/images/dra-vet-800.jpg" srcset="/assets/images/dra-vet-480.jpg 480w, /assets/images/dra-vet-800.jpg 800w"
                     sizes="(max-width: 900px) 280px, 420px" width="420" height="520" fetchpriority="high"
                     alt="Ilustração da <?= e(CLINIC_DRA) ?> cercada de cães, gatos, coelho, tartaruga e papagaio">
            </picture>
            <div class="hero-float-card hero-float-1"><i class="fas fa-heart"></i> Atendimento humanizado</div>
            <div class="hero-float-card hero-float-2"><i class="fas fa-shield-dog"></i> Vacinas em dia</div>
        </div>
    </div>
</section>

<!-- ATALHOS DE SERVIÇOS -->
<section class="section section-tight">
    <div class="container">
        <div class="quick-grid" data-reveal-group>
            <?php foreach ($atalhos as [$href, $icon, $title, $desc]): ?>
            <a href="<?= $href ?>" class="quick-card reveal">
                <span class="quick-icon"><i class="fas <?= $icon ?>"></i></span>
                <span>
                    <strong><?= e($title) ?></strong>
                    <small><?= e($desc) ?></small>
                </span>
                <i class="fas fa-arrow-right quick-arrow"></i>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- SOBRE A DRA -->
<section class="section section-alt">
    <div class="container">
        <div class="about-section">
            <div class="about-image reveal reveal-left">
                <picture>
                    <source type="image/webp" srcset="/assets/images/dra-vet-480.webp">
                    <img src="/assets/images/dra-vet-480.jpg" width="480" height="720" loading="lazy" alt="<?= e(CLINIC_DRA) ?>">
                </picture>
                <div class="about-badge-float">
                    <i class="fas fa-award"></i>
                    <div><strong>10+</strong><span>Anos de experiência</span></div>
                </div>
            </div>
            <div class="about-content reveal reveal-right">
                <span class="section-tag">Quem somos</span>
                <h2>Conheça a <?= e(CLINIC_DRA) ?></h2>
                <p class="dra-title"><i class="fas fa-user-md"></i> Médica Veterinária – <?= e(CLINIC_CRMV) ?></p>
                <p><?= e(CLINIC_ABOUT) ?></p>
                <p>Acredita que cada animal merece atenção individualizada, diagnóstico preciso e tratamento humanizado.</p>
                <div class="about-creds" data-reveal-group>
                    <div class="cred-item reveal"><i class="fas fa-graduation-cap"></i><span>Graduação em Medicina Veterinária</span></div>
                    <div class="cred-item reveal"><i class="fas fa-certificate"></i><span>Especialização em Clínica e Cirurgia de Pequenos Animais</span></div>
                    <div class="cred-item reveal"><i class="fas fa-heart"></i><span>Membro da Associação Brasileira de Medicina Veterinária</span></div>
                    <div class="cred-item reveal"><i class="fas fa-microscope"></i><span>Atualização contínua em Dermatologia e Cardiologia Pet</span></div>
                </div>
                <a href="/pages/servicos.php" class="btn btn-primary">
                    <i class="fas fa-arrow-right"></i> Conhecer os serviços
                </a>
            </div>
        </div>
    </div>
</section>

<!-- PRODUTOS EM DESTAQUE -->
<section class="section">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-tag">Loja Pet</span>
            <h2>Produtos em Destaque</h2>
            <p>Os favoritos da nossa loja — qualidade garantida para o seu pet</p>
        </div>
        <div class="dest-grid scroll-strip" data-reveal-group>
            <?php foreach ($destaques as $p):
                $vis = product_visual($p['nome'], $p['categoria'] ?? '');
            ?>
            <article class="dest-card reveal" style="--accent:<?= e($vis['accent']) ?>;--grad:linear-gradient(<?= e($vis['grad']) ?>);--soft:<?= e($vis['bg']) ?>">
                <div class="dest-img">
                    <div class="dest-img-circle"><i class="fas <?= e($vis['icon']) ?>"></i></div>
                    <span class="dest-img-emoji" aria-hidden="true"><?= e($p['emoji'] ?? '🐾') ?></span>
                    <div class="dest-img-shine"></div>
                </div>
                <div class="dest-body">
                    <h3><?= e($p['nome']) ?></h3>
                    <div class="dest-footer">
                        <span class="dest-price"><?= brl($p['preco']) ?></span>
                        <button class="btn btn-sm btn-comprar" type="button" aria-label="Adicionar <?= e($p['nome']) ?> ao carrinho"
                                data-product="<?= e($p['nome']) ?>" data-price="<?= (float)$p['preco'] ?>" data-emoji="<?= e($p['emoji'] ?? '🐾') ?>">
                            <i class="fas fa-cart-plus"></i>
                        </button>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <div class="center-cta reveal">
            <a href="/pages/loja.php" class="btn btn-outline"><i class="fas fa-shopping-bag"></i> Ver toda a loja</a>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-band reveal">
    <div class="container cta-inner">
        <div>
            <h2>Seu pet precisa de atendimento?</h2>
            <p>Agende online em menos de 1 minuto. Confirmamos pelo WhatsApp.</p>
        </div>
        <div class="cta-actions">
            <a href="/pages/consultas.php" class="btn btn-light"><i class="fas fa-calendar-check"></i> Agendar consulta</a>
            <a href="/pages/tosagem.php" class="btn btn-ghost"><i class="fas fa-scissors"></i> Banho &amp; Tosa</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
