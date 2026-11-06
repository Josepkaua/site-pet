<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/tracker.php';
track_visit('Contato');
$pageTitle  = 'Contato – ' . CLINIC_NAME;
$activePage = 'contato';

const ASSUNTOS = ['Dúvida sobre serviços', 'Informações sobre preços', 'Agendamento', 'Elogio', 'Reclamação', 'Outro'];

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $obj = [
        'nome'     => clean_text($_POST['nome'] ?? '', 120),
        'email'    => clean_email($_POST['email'] ?? ''),
        'assunto'  => pick($_POST['assunto'] ?? '', ASSUNTOS, 'Outro'),
        'mensagem' => clean_text($_POST['mensagem'] ?? '', 3000, true),
        'lido'     => false,
    ];

    if (honeypot_triggered()) {
        $success = 'Mensagem enviada com sucesso! Responderemos em breve.';
        $_POST = [];
    } elseif (!$obj['nome'] || !$obj['mensagem']) {
        $error = 'Por favor, preencha todos os campos obrigatórios.';
    } elseif (!$obj['email']) {
        $error = 'Informe um e-mail válido para podermos responder.';
    } elseif (mb_strlen($obj['mensagem']) < 10) {
        $error = 'Sua mensagem está muito curta. Conte um pouco mais 🙂';
    } elseif (!rate_limit('contato', 5, 3600)) {
        $error = 'Muitas mensagens em pouco tempo. Tente mais tarde ou fale pelo WhatsApp.';
    } else {
        $result = gql('mutation($obj: contatos_insert_input!) { insert_contatos_one(object: $obj) { id } }', ['obj' => $obj]);
        if ($result['ok']) {
            $success = 'Mensagem enviada com sucesso! Responderemos em breve.';
            $_POST = [];
        } else {
            $error = 'Não foi possível enviar agora. Ligue para ' . CLINIC_PHONE . ' ou use o WhatsApp.';
        }
    }
}

require __DIR__ . '/../includes/header.php';
?>

<section class="page-hero">
    <h1 class="anim-up"><i class="fas fa-envelope"></i> Entre em Contato</h1>
    <p class="anim-up" style="--d:.1s">Estamos aqui para tirar suas dúvidas e agendar o atendimento do seu pet.</p>
</section>

<section class="section">
    <div class="container">
        <div class="split-layout split-even">

            <div class="side-stack">
                <div class="form-card contact-card reveal">
                    <h3><i class="fas fa-location-dot"></i> Onde estamos</h3>
                    <a class="contact-line" href="https://www.google.com/maps/search/?api=1&query=<?= rawurlencode(CLINIC_ADDRESS) ?>" target="_blank" rel="noopener noreferrer">
                        <span class="contact-ico"><i class="fas fa-map-marker-alt"></i></span><?= e(CLINIC_ADDRESS) ?>
                    </a>
                    <a class="contact-line" href="tel:+<?= e(CLINIC_WHATS) ?>">
                        <span class="contact-ico"><i class="fas fa-phone"></i></span><?= e(CLINIC_PHONE) ?>
                    </a>
                    <a class="contact-line" href="mailto:<?= e(CLINIC_EMAIL) ?>">
                        <span class="contact-ico"><i class="fas fa-envelope"></i></span><?= e(CLINIC_EMAIL) ?>
                    </a>
                </div>

                <div class="schedule-card reveal">
                    <div class="schedule-card-header"><i class="fas fa-clock"></i><h3>Horários de Funcionamento</h3></div>
                    <table class="schedule-table">
                        <thead><tr><th>Dia</th><th>Consultas</th></tr></thead>
                        <tbody>
                        <?php foreach (horario_resumo('consultas') as $dia => $hr): ?>
                            <tr><td><?= e($dia) ?></td><td><?= e($hr) ?></td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (!empty(horarios()['urgencia'])): ?>
                    <p class="schedule-note"><i class="fas fa-truck-medical"></i> Urgência:
                        <?= e(implode(' · ', array_map(fn($u) => $u['periodo'] . ' ' . $u['abertura'] . '–' . $u['fechamento'], horarios()['urgencia']))) ?>
                    </p>
                    <?php endif; ?>
                </div>

                <div class="btn-stack reveal">
                    <a href="<?= e(whats_link()) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-accent"><i class="fab fa-whatsapp"></i> Falar pelo WhatsApp</a>
                    <a href="<?= e(safe_url(CLINIC_INSTAGRAM)) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline"><i class="fab fa-instagram"></i> Seguir no Instagram</a>
                </div>
            </div>

            <div class="form-card reveal" id="mensagem">
                <h2 class="form-title">Envie uma Mensagem</h2>

                <?php if ($success): ?><div class="alert alert-success" role="status"><i class="fas fa-check-circle"></i> <?= e($success) ?></div><?php endif; ?>
                <?php if ($error):   ?><div class="alert alert-error" role="alert"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div><?php endif; ?>

                <form method="POST" action="#mensagem" class="js-submit-loading">
                    <?= honeypot_field() ?>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="c-nome">Nome *</label>
                            <input id="c-nome" type="text" name="nome" required maxlength="120" autocomplete="name" placeholder="Seu nome" value="<?= old('nome') ?>">
                        </div>
                        <div class="form-group">
                            <label for="c-email">E-mail *</label>
                            <input id="c-email" type="email" name="email" required maxlength="160" autocomplete="email" placeholder="seu@email.com" value="<?= old('email') ?>">
                        </div>
                        <div class="form-group full">
                            <label for="c-assunto">Assunto</label>
                            <select id="c-assunto" name="assunto">
                                <?php foreach (ASSUNTOS as $o): ?><option <?= selected('assunto', $o) ?>><?= e($o) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group full">
                            <label for="c-msg">Mensagem *</label>
                            <textarea id="c-msg" name="mensagem" required minlength="10" maxlength="3000" placeholder="Escreva sua mensagem..." data-counter><?= old('mensagem') ?></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-shine">
                        <i class="fas fa-paper-plane"></i> Enviar Mensagem
                    </button>
                </form>
            </div>

        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
