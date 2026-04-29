<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/tracker.php';
track_visit('Contato');
$pageTitle = 'Contato – ' . CLINIC_NAME;
$activePage = 'contato';

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $obj = [
        'nome'     => htmlspecialchars(trim($_POST['nome']     ?? ''), ENT_QUOTES),
        'email'    => htmlspecialchars(trim($_POST['email']    ?? ''), ENT_QUOTES),
        'assunto'  => htmlspecialchars(trim($_POST['assunto']  ?? ''), ENT_QUOTES),
        'mensagem' => htmlspecialchars(trim($_POST['mensagem'] ?? ''), ENT_QUOTES),
        'lido'     => false,
    ];

    if (!$obj['nome'] || !$obj['email'] || !$obj['mensagem']) {
        $error = 'Por favor, preencha todos os campos obrigatórios.';
    } else {
        $mutation = '
        mutation InsertContato($obj: contatos_insert_input!) {
            insert_contatos_one(object: $obj) { id }
        }';
        $result = gql($mutation, ['obj' => $obj]);
        if ($result['ok']) {
            $success = 'Mensagem enviada com sucesso! Responderemos em breve.';
        } else {
            $error = 'Erro ao enviar. Ligue para ' . CLINIC_PHONE;
        }
    }
}
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<section class="page-hero">
    <h1><i class="fas fa-envelope"></i> Entre em Contato</h1>
    <p>Estamos aqui para tirar suas dúvidas e agendar o atendimento do seu pet.</p>
</section>

<section class="section">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:40px;align-items:start;" class="contact-layout">

            <!-- INFO -->
            <div style="display:flex;flex-direction:column;gap:24px;">
                <div class="form-card" style="padding:32px;">
                    <h3 style="margin-bottom:20px;font-size:1.1rem;">
                        <i class="fas fa-map-marker-alt" style="color:var(--primary)"></i> Localização
                    </h3>
                    <p style="color:var(--text-light);margin-bottom:10px;display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-map-marker-alt" style="color:var(--primary)"></i><?= CLINIC_ADDRESS ?>
                    </p>
                    <p style="color:var(--text-light);margin-bottom:10px;display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-phone" style="color:var(--primary)"></i><?= CLINIC_PHONE ?>
                    </p>
                    <p style="color:var(--text-light);display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-envelope" style="color:var(--primary)"></i><?= CLINIC_EMAIL ?>
                    </p>
                </div>

                <div class="schedule-card">
                    <div class="schedule-card-header">
                        <i class="fas fa-clock"></i>
                        <h3>Horários de Funcionamento</h3>
                    </div>
                    <table class="schedule-table">
                        <thead><tr><th>Dia</th><th>Horário</th></tr></thead>
                        <tbody>
                            <tr><td>Segunda a Sexta</td><td>08:00 – 19:00</td></tr>
                            <tr><td>Sábado</td><td>08:00 – 17:00</td></tr>
                            <tr><td>Domingo</td><td>09:00 – 13:00 (urgência)</td></tr>
                        </tbody>
                    </table>
                </div>

                <div style="display:flex;flex-direction:column;gap:12px;">
                    <a href="https://wa.me/<?= CLINIC_WHATS ?>" target="_blank" class="btn btn-accent">
                        <i class="fab fa-whatsapp"></i> Falar pelo WhatsApp
                    </a>
                    <a href="https://instagram.com" target="_blank" class="btn btn-outline">
                        <i class="fab fa-instagram"></i> Seguir no Instagram
                    </a>
                </div>
            </div>

            <!-- FORM -->
            <div class="form-card">
                <h2 style="margin-bottom:24px;font-size:1.3rem;">Envie uma Mensagem</h2>

                <?php if ($success): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Nome *</label>
                            <input type="text" name="nome" required placeholder="Seu nome">
                        </div>
                        <div class="form-group">
                            <label>E-mail *</label>
                            <input type="email" name="email" required placeholder="seu@email.com">
                        </div>
                        <div class="form-group full">
                            <label>Assunto</label>
                            <select name="assunto">
                                <option>Dúvida sobre serviços</option>
                                <option>Informações sobre preços</option>
                                <option>Agendamento</option>
                                <option>Elogio</option>
                                <option>Reclamação</option>
                                <option>Outro</option>
                            </select>
                        </div>
                        <div class="form-group full">
                            <label>Mensagem *</label>
                            <textarea name="mensagem" required placeholder="Escreva sua mensagem..."></textarea>
                        </div>
                    </div>
                    <br>
                    <button type="submit" class="btn btn-primary" style="width:100%;">
                        <i class="fas fa-paper-plane"></i> Enviar Mensagem
                    </button>
                </form>
            </div>

        </div>
    </div>
</section>

<style>@media(max-width:900px){.contact-layout{grid-template-columns:1fr!important}}</style>

<a href="https://wa.me/<?= CLINIC_WHATS ?>" target="_blank" class="whatsapp-float"><i class="fab fa-whatsapp"></i></a>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
