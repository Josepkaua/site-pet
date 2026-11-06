<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/tracker.php';
track_visit('Banho e Tosa');
$pageTitle  = 'Banho & Tosa – ' . CLINIC_NAME;
$activePage = 'tosagem';

const PORTES = ['P – até 5 kg', 'M – 5 a 15 kg', 'G – 15 a 30 kg', 'GG – acima de 30 kg', 'Gato'];
const SERVICOS_TOSA = ['Banho', 'Tosa Higiênica', 'Tosa Completa', 'Banho + Tosa Higiênica', 'Banho + Tosa Completa', 'Pacote Mensal'];
const HORARIOS_TOSA = ['08:00', '09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00'];

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $obj = [
        'nome'      => clean_text($_POST['nome'] ?? '', 120),
        'telefone'  => clean_phone($_POST['telefone'] ?? ''),
        'pet_nome'  => clean_text($_POST['pet_nome'] ?? '', 80),
        'pet_raca'  => clean_text($_POST['pet_raca'] ?? '', 80),
        'pet_porte' => pick($_POST['pet_porte'] ?? '', PORTES),
        'servico'   => pick($_POST['servico'] ?? '', SERVICOS_TOSA),
        'data'      => clean_future_date($_POST['data'] ?? ''),
        'horario'   => pick($_POST['horario'] ?? '', HORARIOS_TOSA),
        'status'    => 'pendente',
    ];

    if (honeypot_triggered()) {
        $success = 'Agendamento de banho & tosa realizado! Confirmaremos via WhatsApp em breve.';
        $_POST = [];
    } elseif (!$obj['nome'] || !$obj['telefone'] || !$obj['pet_nome'] || !$obj['pet_porte'] || !$obj['servico']) {
        $error = 'Por favor, preencha todos os campos obrigatórios (verifique o telefone).';
    } elseif (!$obj['data']) {
        $error = 'Escolha uma data válida a partir de hoje.';
    } elseif ((horarios()['tosagem'][(int)date('N', strtotime($obj['data'])) - 1]['status'] ?? '') === 'fechado') {
        $error = 'Não atendemos banho & tosa nesse dia da semana. Escolha outra data.';
    } elseif (!rate_limit('tosagem', 5, 3600)) {
        $error = 'Muitas solicitações em pouco tempo. Tente mais tarde ou fale pelo WhatsApp.';
    } else {
        $result = gql('mutation($obj: tosagem_agendamentos_insert_input!) { insert_tosagem_agendamentos_one(object: $obj) { id } }', ['obj' => $obj]);
        if ($result['ok']) {
            $success = 'Banho & tosa de ' . $obj['pet_nome'] . ' agendado! Confirmaremos via WhatsApp em breve.';
            $_POST = [];
        } else {
            $error = 'Não foi possível salvar agora. Ligue para ' . CLINIC_PHONE . ' ou use o WhatsApp.';
        }
    }
}

// Preços da seção "tosagem" (editável no painel), agrupados em Banho / Tosa / Extras
$itens = [];
foreach (precos_secoes() as $s) if ($s['tipo'] === 'tosagem') $itens = $s['itens'];
$grupos = ['Banho' => ['🛁', []], 'Tosa' => ['✂️', []], 'Pacotes & Extras' => ['🌟', []]];
foreach ($itens as $it) {
    $n = mb_strtolower($it['servico']);
    $g = (str_contains($n, '+') || str_contains($n, 'pacote') || !preg_match('/banho|tosa/', $n)) ? 'Pacotes & Extras'
       : (str_starts_with($n, 'tosa') ? 'Tosa' : 'Banho');
    $grupos[$g][1][] = $it;
}
$h = horarios();
$statusLabel = ['aberto' => 'Aberto', 'limitado' => 'Limitado', 'fechado' => 'Fechado'];

require __DIR__ . '/../includes/header.php';
?>

<section class="page-hero">
    <h1 class="anim-up"><i class="fas fa-scissors"></i> Banho &amp; Tosa</h1>
    <p class="anim-up" style="--d:.1s">Seu pet limpo, cheiroso e com aquela carinha feliz que você ama.</p>
</section>

<section class="section">
    <div class="container">

        <div class="section-header reveal">
            <span class="section-tag">Preços</span>
            <h2>Tabela de Banho &amp; Tosa</h2>
            <p>Valores por porte do animal. Pacotes especiais disponíveis.</p>
        </div>

        <div class="pricing-grid spaced" data-reveal-group>
            <?php $i = 0; foreach ($grupos as $titulo => [$emoji, $lista]): if (!$lista) continue; ?>
            <div class="price-card reveal <?= $i++ === 1 ? 'featured' : '' ?>">
                <div class="price-card-header">
                    <div class="category-icon" aria-hidden="true"><?= $emoji ?></div>
                    <h3><?= e($titulo) ?></h3>
                </div>
                <table class="price-table">
                    <?php foreach ($lista as $it): ?>
                    <tr><td><?= e($it['servico']) ?></td><td><?= brl($it['preco']) ?></td></tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="section-header reveal">
            <span class="section-tag">Agenda</span>
            <h2>Horários de Atendimento</h2>
        </div>
        <div class="schedules-grid spaced" data-reveal-group>
            <div class="schedule-card reveal">
                <div class="schedule-card-header"><i class="fas fa-scissors"></i><h3>Banho &amp; Tosa – Horários</h3></div>
                <table class="schedule-table">
                    <thead><tr><th>Dia</th><th>Abertura</th><th>Fechamento</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($h['tosagem'] ?? [] as $idx => $r): $st = $r['status'] ?? 'aberto'; $fech = $st === 'fechado'; ?>
                        <tr <?= (int)date('N') === $idx + 1 ? 'class="is-today"' : '' ?>>
                            <td><?= e($r['dia']) ?></td>
                            <td><?= $fech ? '–' : e($r['abertura']) ?></td>
                            <td><?= $fech ? '–' : e($r['fechamento']) ?></td>
                            <td class="status-<?= $st === 'aberto' ? 'open' : ($st === 'limitado' ? 'limit' : 'closed') ?>"><i class="fas fa-circle"></i> <?= $statusLabel[$st] ?? e($st) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="form-card info-card reveal">
                <h4><i class="fas fa-info-circle"></i> Dicas Importantes</h4>
                <ul class="emoji-list">
                    <li><span>🐾</span> Leve o pet em jejum de pelo menos 2h antes do banho.</li>
                    <li><span>💉</span> Vacina antirrábica obrigatória e em dia.</li>
                    <li><span>⏰</span> Serviço dura de 1h a 3h dependendo do porte.</li>
                    <li><span>📱</span> Avisamos quando seu pet estiver pronto.</li>
                    <li><span>🐶</span> Agendamento obrigatório – sem hora marcada não atendemos.</li>
                </ul>
            </div>
        </div>

        <div class="section-header reveal" id="agendar">
            <span class="section-tag">Agendar</span>
            <h2>Marque seu Horário</h2>
        </div>

        <div class="form-card form-narrow reveal">
            <?php if ($success): ?><div class="alert alert-success" role="status"><i class="fas fa-check-circle"></i> <?= e($success) ?></div><?php endif; ?>
            <?php if ($error):   ?><div class="alert alert-error" role="alert"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div><?php endif; ?>

            <form method="POST" action="#agendar" class="js-submit-loading">
                <?= honeypot_field() ?>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="t-nome">Nome do Tutor *</label>
                        <input id="t-nome" type="text" name="nome" required maxlength="120" autocomplete="name" placeholder="Nome completo" value="<?= old('nome') ?>">
                    </div>
                    <div class="form-group">
                        <label for="t-tel">Telefone / WhatsApp *</label>
                        <input id="t-tel" type="tel" name="telefone" required maxlength="30" autocomplete="tel" data-mask="phone" placeholder="(11) 99999-9999" value="<?= old('telefone') ?>">
                    </div>
                    <div class="form-group">
                        <label for="t-pet">Nome do Pet *</label>
                        <input id="t-pet" type="text" name="pet_nome" required maxlength="80" placeholder="Nome do animal" value="<?= old('pet_nome') ?>">
                    </div>
                    <div class="form-group">
                        <label for="t-raca">Raça</label>
                        <input id="t-raca" type="text" name="pet_raca" maxlength="80" placeholder="Raça do animal" value="<?= old('pet_raca') ?>">
                    </div>
                    <div class="form-group">
                        <label for="t-porte">Porte do Pet *</label>
                        <select id="t-porte" name="pet_porte" required>
                            <option value="">Selecione...</option>
                            <?php foreach (PORTES as $o): ?><option <?= selected('pet_porte', $o) ?>><?= e($o) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="t-serv">Serviço *</label>
                        <select id="t-serv" name="servico" required>
                            <option value="">Selecione...</option>
                            <?php foreach (SERVICOS_TOSA as $o): ?><option <?= selected('servico', $o) ?>><?= e($o) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="t-data">Data Preferida *</label>
                        <input id="t-data" type="date" name="data" min="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d', strtotime('+1 year')) ?>" required value="<?= old('data') ?>">
                    </div>
                    <div class="form-group">
                        <label for="t-hora">Horário Preferido</label>
                        <select id="t-hora" name="horario">
                            <option value="">Qualquer horário</option>
                            <?php foreach (HORARIOS_TOSA as $o): ?><option <?= selected('horario', $o) ?>><?= e($o) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-shine">
                    <i class="fas fa-scissors"></i> Agendar Banho &amp; Tosa
                </button>
            </form>
        </div>

    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
