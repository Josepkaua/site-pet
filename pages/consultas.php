<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/tracker.php';
track_visit('Consultas');
$pageTitle  = 'Agendar Consulta – ' . CLINIC_NAME;
$activePage = 'consultas';

const ESPECIES = ['Cão', 'Gato', 'Ave', 'Réptil', 'Roedor', 'Outro'];
const SERVICOS_CONSULTA = ['Consulta Clínica Geral', 'Vacinação', 'Exames Laboratoriais', 'Ultrassonografia',
                           'Castração', 'Limpeza Dentária', 'Cardiologia', 'Dermatologia', 'Retorno', 'Emergência'];
const HORARIOS_CONSULTA = ['08:00 – 09:00', '09:00 – 10:00', '10:00 – 11:00', '11:00 – 12:00',
                           '13:00 – 14:00', '14:00 – 15:00', '15:00 – 16:00', '16:00 – 17:00'];

// Pré-seleciona serviço vindo da página de serviços
$mapServico = ['Clínica Geral' => 'Consulta Clínica Geral', 'Cirurgias' => 'Castração', 'Odontologia' => 'Limpeza Dentária'];
if (!isset($_POST['servico']) && isset($_GET['servico'])) {
    $g = (string)$_GET['servico'];
    $_POST['servico'] = pick($mapServico[$g] ?? $g, SERVICOS_CONSULTA);
}

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $obj = [
        'nome'        => clean_text($_POST['nome'] ?? '', 120),
        'telefone'    => clean_phone($_POST['telefone'] ?? ''),
        'email'       => clean_email($_POST['email'] ?? ''),
        'pet_nome'    => clean_text($_POST['pet_nome'] ?? '', 80),
        'pet_especie' => pick($_POST['pet_especie'] ?? '', ESPECIES),
        'pet_raca'    => clean_text($_POST['pet_raca'] ?? '', 80),
        'servico'     => pick($_POST['servico'] ?? '', SERVICOS_CONSULTA),
        'data'        => clean_future_date($_POST['data'] ?? ''),
        'horario'     => pick($_POST['horario'] ?? '', HORARIOS_CONSULTA),
        'obs'         => clean_text($_POST['obs'] ?? '', 1000, true),
        'status'      => 'pendente',
    ];

    if (honeypot_triggered()) {
        $success = 'Agendamento realizado com sucesso! Confirmaremos via WhatsApp em breve.';   // finge sucesso para robôs
        $_POST = [];
    } elseif (!$obj['nome'] || !$obj['telefone'] || !$obj['pet_nome'] || !$obj['pet_especie'] || !$obj['servico']) {
        $error = 'Preencha todos os campos obrigatórios (verifique o telefone).';
    } elseif (!$obj['data']) {
        $error = 'Escolha uma data válida a partir de hoje.';
    } elseif (!empty($_POST['email']) && !$obj['email']) {
        $error = 'O e-mail informado não é válido.';
    } elseif (!rate_limit('agendamento', 5, 3600)) {
        $error = 'Muitas solicitações em pouco tempo. Tente mais tarde ou fale pelo WhatsApp.';
    } else {
        $result = gql('mutation($obj: agendamentos_insert_input!) { insert_agendamentos_one(object: $obj) { id } }', ['obj' => $obj]);
        if ($result['ok']) {
            $success = 'Agendamento de ' . $obj['pet_nome'] . ' realizado! Confirmaremos via WhatsApp em breve.';
            $_POST = [];
        } else {
            $error = 'Não foi possível salvar agora. Ligue para ' . CLINIC_PHONE . ' ou use o WhatsApp.';
        }
    }
}

require __DIR__ . '/../includes/header.php';
?>

<section class="page-hero">
    <h1 class="anim-up"><i class="fas fa-calendar-check"></i> Agendar Consulta</h1>
    <p class="anim-up" style="--d:.1s">Preencha o formulário e entraremos em contato para confirmar seu horário.</p>
</section>

<section class="section">
    <div class="container">
        <div class="split-layout">

            <div class="form-card reveal">
                <h2 class="form-title">Dados do Agendamento</h2>
                <p class="form-sub">Campos com * são obrigatórios.</p>

                <?php if ($success): ?><div class="alert alert-success" role="status"><i class="fas fa-check-circle"></i> <?= e($success) ?></div><?php endif; ?>
                <?php if ($error):   ?><div class="alert alert-error" role="alert"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div><?php endif; ?>

                <form method="POST" class="js-submit-loading">
                    <?= honeypot_field() ?>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="f-nome">Nome do Tutor *</label>
                            <input id="f-nome" type="text" name="nome" placeholder="Nome completo" maxlength="120" autocomplete="name" required value="<?= old('nome') ?>">
                        </div>
                        <div class="form-group">
                            <label for="f-tel">Telefone / WhatsApp *</label>
                            <input id="f-tel" type="tel" name="telefone" placeholder="(11) 99999-9999" maxlength="30" autocomplete="tel" data-mask="phone" required value="<?= old('telefone') ?>">
                        </div>
                        <div class="form-group full">
                            <label for="f-email">E-mail</label>
                            <input id="f-email" type="email" name="email" placeholder="seu@email.com" maxlength="160" autocomplete="email" value="<?= old('email') ?>">
                        </div>
                        <div class="form-group">
                            <label for="f-pet">Nome do Pet *</label>
                            <input id="f-pet" type="text" name="pet_nome" placeholder="Nome do animal" maxlength="80" required value="<?= old('pet_nome') ?>">
                        </div>
                        <div class="form-group">
                            <label for="f-esp">Espécie *</label>
                            <select id="f-esp" name="pet_especie" required>
                                <option value="">Selecione...</option>
                                <?php foreach (ESPECIES as $o): ?><option <?= selected('pet_especie', $o) ?>><?= e($o) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="f-raca">Raça</label>
                            <input id="f-raca" type="text" name="pet_raca" placeholder="Raça do animal" maxlength="80" value="<?= old('pet_raca') ?>">
                        </div>
                        <div class="form-group">
                            <label for="f-serv">Serviço *</label>
                            <select id="f-serv" name="servico" required>
                                <option value="">Selecione...</option>
                                <?php foreach (SERVICOS_CONSULTA as $o): ?><option <?= selected('servico', $o) ?>><?= e($o) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="f-data">Data Preferida *</label>
                            <input id="f-data" type="date" name="data" min="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d', strtotime('+1 year')) ?>" required value="<?= old('data') ?>">
                        </div>
                        <div class="form-group">
                            <label for="f-hora">Horário Preferido</label>
                            <select id="f-hora" name="horario">
                                <option value="">Qualquer horário</option>
                                <?php foreach (HORARIOS_CONSULTA as $o): ?><option <?= selected('horario', $o) ?>><?= e($o) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group full">
                            <label for="f-obs">Observações / Sintomas</label>
                            <textarea id="f-obs" name="obs" maxlength="1000" placeholder="Descreva o motivo da consulta..."><?= old('obs') ?></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-shine">
                        <i class="fas fa-calendar-check"></i> Confirmar Agendamento
                    </button>
                </form>
            </div>

            <aside class="side-stack">
                <div class="schedule-card reveal">
                    <div class="schedule-card-header"><i class="fas fa-clock"></i><h3>Horários de Consulta</h3></div>
                    <table class="schedule-table">
                        <thead><tr><th>Dia</th><th>Horário</th></tr></thead>
                        <tbody>
                        <?php foreach (horario_resumo('consultas') as $dia => $hr): ?>
                            <tr><td><?= e($dia) ?></td><td><?= e($hr) ?></td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="form-card info-card reveal">
                    <h4><i class="fas fa-info-circle"></i> Informações</h4>
                    <ul class="check-list">
                        <li>Confirmação via WhatsApp em até 2h.</li>
                        <li>Traga a carteirinha de vacinas do pet.</li>
                        <li>Chegue 10 minutos antes.</li>
                        <li>Cancelamentos com 24h de antecedência.</li>
                    </ul>
                </div>
                <a href="<?= e(whats_link('Olá! Gostaria de agendar uma consulta.')) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-accent btn-block reveal">
                    <i class="fab fa-whatsapp"></i> Agendar pelo WhatsApp
                </a>
            </aside>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
