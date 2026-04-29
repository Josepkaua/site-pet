<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/tracker.php';
track_visit('Banho e Tosa');
$pageTitle = 'Banho & Tosa – ' . CLINIC_NAME;
$activePage = 'tosagem';

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $obj = [
        'nome'      => htmlspecialchars(trim($_POST['nome']      ?? ''), ENT_QUOTES),
        'telefone'  => htmlspecialchars(trim($_POST['telefone']  ?? ''), ENT_QUOTES),
        'pet_nome'  => htmlspecialchars(trim($_POST['pet_nome']  ?? ''), ENT_QUOTES),
        'pet_raca'  => htmlspecialchars(trim($_POST['pet_raca']  ?? ''), ENT_QUOTES),
        'pet_porte' => htmlspecialchars(trim($_POST['pet_porte'] ?? ''), ENT_QUOTES),
        'servico'   => htmlspecialchars(trim($_POST['servico']   ?? ''), ENT_QUOTES),
        'data'      => htmlspecialchars(trim($_POST['data']      ?? ''), ENT_QUOTES),
        'horario'   => htmlspecialchars(trim($_POST['horario']   ?? ''), ENT_QUOTES),
        'status'    => 'pendente',
    ];

    if (!$obj['nome'] || !$obj['telefone'] || !$obj['pet_nome'] || !$obj['data']) {
        $error = 'Por favor, preencha todos os campos obrigatórios.';
    } else {
        $mutation = '
        mutation InsertTosagem($obj: tosagem_agendamentos_insert_input!) {
            insert_tosagem_agendamentos_one(object: $obj) { id }
        }';
        $result = gql($mutation, ['obj' => $obj]);
        if ($result['ok']) {
            $success = 'Agendamento de banho & tosa realizado! Confirmaremos via WhatsApp em breve.';
        } else {
            $error = 'Erro ao agendar. Ligue para ' . CLINIC_PHONE;
        }
    }
}
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<section class="page-hero">
    <h1><i class="fas fa-scissors"></i> Banho & Tosa</h1>
    <p>Seu pet limpo, cheiroso e com aquela carinha feliz que você ama.</p>
</section>

<section class="section">
    <div class="container">

        <!-- PREÇOS -->
        <div class="section-header">
            <span class="section-tag">Preços</span>
            <h2>Tabela de Banho & Tosa</h2>
            <p>Valores por porte do animal. Pacotes especiais disponíveis.</p>
        </div>

        <div class="pricing-grid" style="margin-bottom:60px;">
            <div class="price-card">
                <div class="price-card-header">
                    <div class="category-icon">🛁</div>
                    <h3>Banho</h3>
                </div>
                <table class="price-table">
                    <tr><td>Porte P – até 5 kg</td><td>R$ 50,00</td></tr>
                    <tr><td>Porte M – 5 a 15 kg</td><td>R$ 70,00</td></tr>
                    <tr><td>Porte G – 15 a 30 kg</td><td>R$ 90,00</td></tr>
                    <tr><td>Porte GG – acima de 30 kg</td><td>R$ 120,00</td></tr>
                    <tr><td>Gatos (qualquer porte)</td><td>R$ 80,00</td></tr>
                </table>
            </div>
            <div class="price-card featured">
                <div class="price-card-header">
                    <div class="category-icon">✂️</div>
                    <h3>Tosa</h3>
                </div>
                <table class="price-table">
                    <tr><td>Tosa Higiênica – P</td><td>R$ 40,00</td></tr>
                    <tr><td>Tosa Higiênica – G</td><td>R$ 60,00</td></tr>
                    <tr><td>Tosa Completa – P</td><td>R$ 80,00</td></tr>
                    <tr><td>Tosa Completa – M</td><td>R$ 100,00</td></tr>
                    <tr><td>Tosa Completa – G</td><td>R$ 130,00</td></tr>
                    <tr><td>Tosa Completa – GG</td><td>R$ 160,00</td></tr>
                </table>
            </div>
            <div class="price-card">
                <div class="price-card-header">
                    <div class="category-icon">🌟</div>
                    <h3>Pacotes & Extras</h3>
                </div>
                <table class="price-table">
                    <tr><td>Banho + Tosa Higiênica</td><td>R$ 85,00</td></tr>
                    <tr><td>Banho + Tosa Completa – P</td><td>R$ 120,00</td></tr>
                    <tr><td>Pacote Mensal (4 banhos)</td><td>R$ 180,00</td></tr>
                    <tr><td>Hidratação de Pelagem</td><td>R$ 35,00</td></tr>
                    <tr><td>Limpeza de Ouvidos</td><td>R$ 25,00</td></tr>
                    <tr><td>Corte de Unhas</td><td>R$ 20,00</td></tr>
                </table>
            </div>
        </div>

        <!-- HORÁRIOS -->
        <div class="section-header">
            <span class="section-tag">Agenda</span>
            <h2>Horários de Atendimento</h2>
        </div>
        <div class="schedules-grid" style="margin-bottom:60px;">
            <div class="schedule-card">
                <div class="schedule-card-header">
                    <i class="fas fa-scissors"></i>
                    <h3>Banho & Tosa – Horários</h3>
                </div>
                <table class="schedule-table">
                    <thead><tr><th>Dia</th><th>Abertura</th><th>Fechamento</th><th>Status</th></tr></thead>
                    <tbody>
                        <tr><td>Segunda-feira</td><td>08:00</td><td>17:00</td><td class="status-open"><i class="fas fa-circle"></i> Aberto</td></tr>
                        <tr><td>Terça-feira</td><td>08:00</td><td>17:00</td><td class="status-open"><i class="fas fa-circle"></i> Aberto</td></tr>
                        <tr><td>Quarta-feira</td><td>08:00</td><td>17:00</td><td class="status-open"><i class="fas fa-circle"></i> Aberto</td></tr>
                        <tr><td>Quinta-feira</td><td>08:00</td><td>17:00</td><td class="status-open"><i class="fas fa-circle"></i> Aberto</td></tr>
                        <tr><td>Sexta-feira</td><td>08:00</td><td>16:00</td><td class="status-open"><i class="fas fa-circle"></i> Aberto</td></tr>
                        <tr><td>Sábado</td><td>08:00</td><td>14:00</td><td class="status-limit"><i class="fas fa-circle"></i> Limitado</td></tr>
                        <tr><td>Domingo</td><td>–</td><td>–</td><td class="status-closed"><i class="fas fa-circle"></i> Fechado</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="form-card" style="padding:28px;">
                <h4 style="margin-bottom:16px;"><i class="fas fa-info-circle" style="color:var(--primary)"></i> Dicas Importantes</h4>
                <p style="font-size:.85rem;color:var(--text-light);margin-bottom:10px;">🐾 Leve o pet em jejum de pelo menos 2h antes do banho.</p>
                <p style="font-size:.85rem;color:var(--text-light);margin-bottom:10px;">💉 Vacina antirrábica obrigatória e em dia.</p>
                <p style="font-size:.85rem;color:var(--text-light);margin-bottom:10px;">⏰ Serviço dura de 1h a 3h dependendo do porte.</p>
                <p style="font-size:.85rem;color:var(--text-light);margin-bottom:10px;">📱 Avisamos quando seu pet estiver pronto.</p>
                <p style="font-size:.85rem;color:var(--text-light);">🐶 Agendamento obrigatório – sem hora marcada não atendemos.</p>
            </div>
        </div>

        <!-- FORM AGENDAMENTO -->
        <div class="section-header">
            <span class="section-tag">Agendar</span>
            <h2>Marque seu Horário</h2>
        </div>

        <div class="form-card">
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
                        <input type="text" name="nome" required placeholder="Nome completo">
                    </div>
                    <div class="form-group">
                        <label>Telefone / WhatsApp *</label>
                        <input type="tel" name="telefone" required placeholder="(11) 99999-9999">
                    </div>
                    <div class="form-group">
                        <label>Nome do Pet *</label>
                        <input type="text" name="pet_nome" required placeholder="Nome do animal">
                    </div>
                    <div class="form-group">
                        <label>Raça</label>
                        <input type="text" name="pet_raca" placeholder="Raça do animal">
                    </div>
                    <div class="form-group">
                        <label>Porte do Pet *</label>
                        <select name="pet_porte" required>
                            <option value="">Selecione...</option>
                            <option>P – até 5 kg</option>
                            <option>M – 5 a 15 kg</option>
                            <option>G – 15 a 30 kg</option>
                            <option>GG – acima de 30 kg</option>
                            <option>Gato</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Serviço *</label>
                        <select name="servico" required>
                            <option value="">Selecione...</option>
                            <option>Banho</option>
                            <option>Tosa Higiênica</option>
                            <option>Tosa Completa</option>
                            <option>Banho + Tosa Higiênica</option>
                            <option>Banho + Tosa Completa</option>
                            <option>Pacote Mensal</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Data Preferida *</label>
                        <input type="date" name="data" min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Horário Preferido</label>
                        <select name="horario">
                            <option value="">Qualquer horário</option>
                            <option>08:00</option>
                            <option>09:00</option>
                            <option>10:00</option>
                            <option>11:00</option>
                            <option>13:00</option>
                            <option>14:00</option>
                            <option>15:00</option>
                            <option>16:00</option>
                        </select>
                    </div>
                </div>
                <br>
                <button type="submit" class="btn btn-primary" style="width:100%;">
                    <i class="fas fa-scissors"></i> Agendar Banho & Tosa
                </button>
            </form>
        </div>

    </div>
</section>

<a href="https://wa.me/<?= CLINIC_WHATS ?>" target="_blank" class="whatsapp-float"><i class="fab fa-whatsapp"></i></a>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
