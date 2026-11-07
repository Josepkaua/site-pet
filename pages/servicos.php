<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/tracker.php';
track_visit('Serviços');
$pageTitle  = 'Serviços – ' . CLINIC_NAME;
$activePage = 'servicos';

$servicos = [
    ['fa-stethoscope', 'Clínica Geral',        'Consultas clínicas completas para diagnóstico e tratamento.'],
    ['fa-syringe',     'Vacinação',            'Calendário vacinal atualizado para cães, gatos e outros animais.'],
    ['fa-flask',       'Exames Laboratoriais', 'Hemograma, bioquímica, urinálise e parasitológico.'],
    ['fa-x-ray',       'Ultrassonografia',     'Diagnóstico por imagem para avaliação interna detalhada.'],
    ['fa-cut',         'Cirurgias',            'Castração, cirurgias eletivas e emergências cirúrgicas.'],
    ['fa-tooth',       'Odontologia',          'Limpeza dentária, extração e tratamento periodontal.'],
    ['fa-heart-pulse', 'Cardiologia',          'Eletrocardiograma e avaliação cardiovascular especializada.'],
    ['fa-hand-dots',   'Dermatologia',         'Tratamento de alergias, dermatites e doenças de pele.'],
];
$h = horarios();
$statusLabel = ['aberto' => 'Aberto', 'limitado' => 'Limitado', 'fechado' => 'Fechado'];

require __DIR__ . '/../includes/header.php';
?>

<section class="page-hero">
    <h1 class="anim-up"><i class="fas fa-stethoscope"></i> Nossos Serviços</h1>
    <p class="anim-up" style="--d:.1s">Tudo o que o seu pet precisa, com qualidade e carinho profissional.</p>
    <nav class="page-jump anim-up" style="--d:.2s" aria-label="Nesta página">
        <a href="#horarios"><i class="fas fa-clock"></i> Horários</a>
        <a href="#precos"><i class="fas fa-tags"></i> Preços</a>
        <a href="/pages/consultas.php"><i class="fas fa-calendar-check"></i> Agendar</a>
    </nav>
</section>

<section class="section">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-tag">Saúde &amp; Bem-estar</span>
            <h2>Serviços Clínicos</h2>
        </div>
        <div class="services-grid" data-reveal-group>
            <?php foreach ($servicos as [$icon, $title, $desc]): ?>
            <a href="/pages/consultas.php?servico=<?= rawurlencode($title) ?>" class="service-card reveal">
                <div class="service-icon"><i class="fas <?= $icon ?>"></i></div>
                <h3><?= e($title) ?></h3>
                <p><?= e($desc) ?></p>
                <span class="service-link">Agendar <i class="fas fa-arrow-right"></i></span>
            </a>
            <?php endforeach; ?>
            <a href="<?= e(whats_link('Olá! Preciso de atendimento de urgência.')) ?>" target="_blank" rel="noopener noreferrer" class="service-card service-card-urgent reveal">
                <div class="service-icon"><i class="fas fa-truck-medical"></i></div>
                <h3>Urgência &amp; Emergência</h3>
                <p>Atendimento emergencial, inclusive nos finais de semana.</p>
                <span class="service-link">Chamar no WhatsApp <i class="fas fa-arrow-right"></i></span>
            </a>
        </div>
    </div>
</section>

<section class="section section-alt" id="horarios">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-tag">Agenda</span>
            <h2>Horários de Atendimento</h2>
            <p>Confira os horários disponíveis para cada tipo de serviço.</p>
        </div>
        <div class="schedules-grid" data-reveal-group>
            <?php foreach (['consultas' => ['fa-stethoscope', 'Consultas Veterinárias'], 'tosagem' => ['fa-scissors', 'Banho & Tosa']] as $tipo => [$icon, $titulo]): ?>
            <details class="schedule-card acc reveal" open>
                <summary class="schedule-card-header"><i class="fas <?= $icon ?>"></i><h3><?= e($titulo) ?></h3><i class="fas fa-chevron-down acc-arrow"></i></summary>
                <table class="schedule-table">
                    <thead><tr><th>Dia</th><th>Horário</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($h[$tipo] ?? [] as $idx => $r): $st = $r['status'] ?? 'aberto'; ?>
                        <tr <?= (int)date('N') === $idx + 1 ? 'class="is-today"' : '' ?>>
                            <td><?= e($r['dia']) ?></td>
                            <td><?= $st === 'fechado' || !$r['abertura'] ? '–' : e($r['abertura'] . ' – ' . $r['fechamento']) ?></td>
                            <td class="status-<?= $st === 'aberto' ? 'open' : ($st === 'limitado' ? 'limit' : 'closed') ?>">
                                <i class="fas fa-circle"></i> <?= $statusLabel[$st] ?? e($st) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </details>
            <?php endforeach; ?>

            <details class="schedule-card schedule-card-urgent acc reveal" open>
                <summary class="schedule-card-header"><i class="fas fa-truck-medical"></i><h3>Urgência</h3><i class="fas fa-chevron-down acc-arrow"></i></summary>
                <table class="schedule-table">
                    <thead><tr><th>Período</th><th>Horário</th><th>Contato</th></tr></thead>
                    <tbody>
                    <?php foreach ($h['urgencia'] ?? [] as $r): ?>
                        <tr>
                            <td><?= e($r['periodo']) ?></td>
                            <td><?= e($r['abertura'] . ' – ' . $r['fechamento']) ?></td>
                            <td><a href="tel:+<?= e(CLINIC_WHATS) ?>"><?= e(CLINIC_PHONE) ?></a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </details>
        </div>
    </div>
</section>

<section class="section" id="precos">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-tag">Transparência</span>
            <h2>Tabela de Preços</h2>
            <p>Valores de referência. Procedimentos específicos podem variar após avaliação.</p>
        </div>
        <div class="pricing-grid" data-reveal-group>
            <?php foreach (precos_secoes() as $i => $sec): if (empty($sec['itens'])) continue; ?>
            <details class="price-card acc reveal <?= $i === 1 ? 'featured' : '' ?>" style="--sec:<?= e($sec['color'] ?? '#2e9e6b') ?>" open>
                <summary class="price-card-header">
                    <div class="category-icon"><i class="fas fa-<?= e($sec['icon'] ?? 'list') ?>"></i></div>
                    <h3><?= e($sec['titulo']) ?> <small class="acc-count"><?= count($sec['itens']) ?> itens</small></h3>
                    <i class="fas fa-chevron-down acc-arrow"></i>
                </summary>
                <table class="price-table">
                    <?php foreach ($sec['itens'] as $it): ?>
                    <tr><td><?= e($it['servico']) ?></td><td><?= brl($it['preco']) ?></td></tr>
                    <?php endforeach; ?>
                </table>
            </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
