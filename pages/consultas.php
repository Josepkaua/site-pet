<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/tracker.php';
track_visit('Consultas');
$pageTitle = 'Agendar Consulta – ' . CLINIC_NAME;
$activePage = 'consultas';

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $campos = ['nome','telefone','email','pet_nome','pet_especie','pet_raca','servico','data','horario','obs'];
    $obj = [];
    foreach ($campos as $f) {
        $obj[$f] = htmlspecialchars(trim($_POST[$f] ?? ''), ENT_QUOTES);
    }
    $obj['status'] = 'pendente';

    if (!$obj['nome'] || !$obj['telefone'] || !$obj['pet_nome'] || !$obj['data']) {
        $error = 'Preencha todos os campos obrigatórios.';
    } else {
        $mutation = '
        mutation InsertAgendamento($obj: agendamentos_insert_input!) {
            insert_agendamentos_one(object: $obj) { id }
        }';
        $result = gql($mutation, ['obj' => $obj]);
        if ($result['ok']) {
            $success = 'Agendamento realizado com sucesso! Confirmaremos via WhatsApp em breve.';
        } else {
            $error = 'Erro ao agendar. Ligue para ' . CLINIC_PHONE;
        }
    }
}
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<section class="page-hero">
    <h1><i class="fas fa-calendar-check"></i> Agendar Consulta</h1>
    <p>Preencha o formulário e entraremos em contato para confirmar seu horário.</p>
</section>

<section class="section">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 380px;gap:40px;align-items:start;" class="consult-layout">

            <div class="form-card">
                <h2 style="margin-bottom:8px;font-size:1.3rem;">Dados do Agendamento</h2>
                <p style="color:var(--text-light);font-size:.88rem;margin-bottom:28px;">Campos com * são obrigatórios.</p>

                <?php if ($success): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Nome do Tutor *</label>
                            <input type="text" name="nome" placeholder="Nome completo" required>
                        </div>
                        <div class="form-group">
                            <label>Telefone / WhatsApp *</label>
                            <input type="tel" name="telefone" placeholder="(11) 99999-9999" required>
                        </div>
                        <div class="form-group full">
                            <label>E-mail</label>
                            <input type="email" name="email" placeholder="seu@email.com">
                        </div>
                        <div class="form-group">
                            <label>Nome do Pet *</label>
                            <input type="text" name="pet_nome" placeholder="Nome do animal" required>
                        </div>
                        <div class="form-group">
                            <label>Espécie *</label>
                            <select name="pet_especie" required>
                                <option value="">Selecione...</option>
                                <option>Cão</option>
                                <option>Gato</option>
                                <option>Ave</option>
                                <option>Réptil</option>
                                <option>Roedor</option>
                                <option>Outro</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Raça</label>
                            <input type="text" name="pet_raca" placeholder="Raça do animal">
                        </div>
                        <div class="form-group">
                            <label>Serviço *</label>
                            <select name="servico" required>
                                <option value="">Selecione...</option>
                                <option>Consulta Clínica Geral</option>
                                <option>Vacinação</option>
                                <option>Exames Laboratoriais</option>
                                <option>Ultrassonografia</option>
                                <option>Castração</option>
                                <option>Limpeza Dentária</option>
                                <option>Retorno</option>
                                <option>Emergência</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Data Preferida *</label>
                            <input type="date" name="data" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="form-group full">
                            <label>Horário Preferido</label>
                            <select name="horario">
                                <option value="">Qualquer horário</option>
                                <option>08:00 – 09:00</option>
                                <option>09:00 – 10:00</option>
                                <option>10:00 – 11:00</option>
                                <option>11:00 – 12:00</option>
                                <option>13:00 – 14:00</option>
                                <option>14:00 – 15:00</option>
                                <option>15:00 – 16:00</option>
                                <option>16:00 – 17:00</option>
                            </select>
                        </div>
                        <div class="form-group full">
                            <label>Observações / Sintomas</label>
                            <textarea name="obs" placeholder="Descreva o motivo da consulta..."></textarea>
                        </div>
                    </div>
                    <br>
                    <button type="submit" class="btn btn-primary" style="width:100%;">
                        <i class="fas fa-calendar-check"></i> Confirmar Agendamento
                    </button>
                </form>
            </div>

            <div style="display:flex;flex-direction:column;gap:20px;">
                <div class="schedule-card">
                    <div class="schedule-card-header">
                        <i class="fas fa-clock"></i>
                        <h3>Horários Disponíveis</h3>
                    </div>
                    <table class="schedule-table">
                        <thead><tr><th>Dia</th><th>Horário</th></tr></thead>
                        <tbody>
                            <tr><td>Seg – Sex</td><td>08:00 – 18:00</td></tr>
                            <tr><td>Sábado</td><td>08:00 – 13:00</td></tr>
                            <tr><td>Domingo</td><td>Fechado</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="form-card" style="padding:24px;">
                    <h4 style="margin-bottom:12px;"><i class="fas fa-info-circle" style="color:var(--primary)"></i> Informações</h4>
                    <p style="font-size:.85rem;color:var(--text-light);margin-bottom:8px;">• Confirmação via WhatsApp em até 2h.</p>
                    <p style="font-size:.85rem;color:var(--text-light);margin-bottom:8px;">• Traga a carteirinha de vacinas do pet.</p>
                    <p style="font-size:.85rem;color:var(--text-light);margin-bottom:8px;">• Chegue 10 minutos antes.</p>
                    <p style="font-size:.85rem;color:var(--text-light);">• Cancelamentos com 24h de antecedência.</p>
                </div>
                <a href="https://wa.me/5511999999999?text=Olá! Gostaria de agendar uma consulta."
                   target="_blank" class="btn btn-accent" style="width:100%;justify-content:center;">
                    <i class="fab fa-whatsapp"></i> Agendar pelo WhatsApp
                </a>
            </div>
        </div>
    </div>
</section>

<style>@media(max-width:900px){.consult-layout{grid-template-columns:1fr!important}}</style>

<a href="https://wa.me/5511999999999" target="_blank" class="whatsapp-float"><i class="fab fa-whatsapp"></i></a>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
