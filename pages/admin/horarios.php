<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
auth_admin();

$adminPage  = 'horarios';
$adminTitle = 'Horários de Atendimento';
$adminSub   = 'Edite os horários que aparecem no site para os clientes';
require_once __DIR__ . '/../../includes/admin_layout.php';

$horarios = horarios();
$success  = $error = '';

// Aceita só HH:MM válido
$hhmm = fn($v) => (is_string($v) && preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $v)) ? $v : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = pick($_POST['tipo'] ?? '', ['consultas', 'tosagem', 'urgencia']);
    $ab   = (array)($_POST['abertura']   ?? []);
    $fe   = (array)($_POST['fechamento'] ?? []);
    $st   = (array)($_POST['status']     ?? []);
    $invalido = false;

    // Os nomes dos dias/períodos vêm do arquivo, não do formulário (não podem ser adulterados).
    // Os campos usam índice explícito (abertura[3]) para não desalinhar quando um dia está "fechado".
    if ($tipo === 'consultas' || $tipo === 'tosagem') {
        foreach ($horarios[$tipo] as $i => &$row) {
            $row['status'] = pick($st[$i] ?? '', ['aberto', 'limitado', 'fechado'], $row['status'] ?? 'aberto');
            if ($row['status'] === 'fechado') {
                $row['abertura'] = $row['fechamento'] = '';
                continue;
            }
            $row['abertura']   = $hhmm($ab[$i] ?? '');
            $row['fechamento'] = $hhmm($fe[$i] ?? '');
            if (!$row['abertura'] || !$row['fechamento'] || $row['abertura'] >= $row['fechamento']) $invalido = $row['dia'];
        }
        unset($row);
    } elseif ($tipo === 'urgencia') {
        foreach ($horarios['urgencia'] as $i => &$row) {
            $row['abertura']   = $hhmm($ab[$i] ?? '');
            $row['fechamento'] = $hhmm($fe[$i] ?? '');
            if (!$row['abertura'] || !$row['fechamento']) $invalido = $row['periodo'];
        }
        unset($row);
    }

    if (!$tipo) {
        $error = 'Formulário inválido.';
    } elseif ($invalido) {
        $error = 'Verifique os horários de "' . $invalido . '": a abertura deve ser antes do fechamento.';
        $horarios = horarios();
    } elseif (save_json('horarios', $horarios)) {
        $success = 'Horários atualizados! O site já reflete as mudanças.';
    } else {
        $error = 'Erro ao salvar. Verifique as permissões da pasta data/.';
    }
}
?>

<?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= e($success) ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div><?php endif; ?>

<style>
.hor-hd{display:flex;align-items:center;gap:14px;margin-bottom:18px;padding-bottom:16px;border-bottom:2px solid var(--bg2);}
.hor-hd-icon{width:48px;height:48px;border-radius:13px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.3rem;flex-shrink:0;}
.hor-hd h2{font-size:1.05rem;font-weight:700;margin:0;}
.hor-hd p{font-size:.8rem;color:var(--text-light);margin:2px 0 0;}
.hor-thead{display:grid;grid-template-columns:190px 1fr 1fr 170px;gap:0;background:var(--bg2);border-radius:10px 10px 0 0;padding:9px 18px;margin-bottom:3px;}
.hor-thead span{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-light);}
.hor-row{display:grid;grid-template-columns:190px 1fr 1fr 170px;gap:0;align-items:center;background:#fff;border-radius:10px;padding:13px 18px;margin-bottom:5px;box-shadow:0 2px 8px rgba(0,0,0,.04);border:1.5px solid transparent;transition:.2s;}
.hor-row-purple:hover{border-color:#8e44ad!important;}
.hor-row:hover{border-color:var(--primary);box-shadow:0 4px 18px rgba(46,158,107,.12);}
.hor-day{display:flex;align-items:center;gap:10px;font-weight:600;font-size:.9rem;}
.hor-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;}
.t-wrap{display:flex;align-items:center;gap:7px;}
.t-wrap i{color:var(--text-light);font-size:.8rem;width:14px;}
.t-wrap input[type="time"]{border:2px solid #eee;border-radius:9px;padding:7px 10px;font-size:.87rem;font-family:inherit;color:var(--text);background:var(--bg);transition:.2s;width:116px;}
.t-wrap input[type="time"]:focus{outline:none;border-color:var(--primary);}
.st-sel{border-radius:50px;padding:6px 28px 6px 13px;font-size:.8rem;font-family:inherit;font-weight:700;cursor:pointer;border:2px solid;transition:.2s;appearance:none;background:var(--bg) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'%3E%3Cpath fill='%236b7f8e' d='M5 7L0 2h10z'/%3E%3C/svg%3E") no-repeat right 10px center;}
.st-sel:focus{outline:none;}
.st-sel.s-aberto  {border-color:#27ae60;color:#1e8449;background-color:#d5f5e3;}
.st-sel.s-limitado{border-color:#f39c12;color:#9a7d0a;background-color:#fef9e7;}
.st-sel.s-fechado {border-color:#e74c3c;color:#922b21;background-color:#fadbd8;}
.hor-foot{display:flex;justify-content:flex-end;padding-top:14px;}
.urg-thead{display:grid;grid-template-columns:190px 1fr 1fr;gap:0;background:#fadbd8;border-radius:10px 10px 0 0;padding:9px 18px;margin-bottom:3px;}
.urg-thead span{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#922b21;}
.urg-row{display:grid;grid-template-columns:190px 1fr 1fr;gap:0;align-items:center;background:#fff;border-radius:10px;padding:13px 18px;margin-bottom:5px;box-shadow:0 2px 8px rgba(0,0,0,.04);border:1.5px solid transparent;transition:.2s;}
.urg-row:hover{border-color:#e74c3c;}
@media(max-width:680px){
    .hor-thead,.hor-row,.urg-thead,.urg-row{grid-template-columns:1fr;gap:10px;}
}
</style>

<!-- CONSULTAS -->
<div class="panel-card" style="margin-bottom:24px;">
    <div class="panel-card-body">
        <div class="hor-hd">
            <div class="hor-hd-icon" style="background:linear-gradient(135deg,var(--primary),var(--primary-dk));">
                <i class="fas fa-stethoscope"></i>
            </div>
            <div>
                <h2>Horários de Consultas</h2>
                <p>Atendimento veterinário – configure abertura, fechamento e status por dia</p>
            </div>
        </div>
        <form method="POST">
            <input type="hidden" name="tipo" value="consultas">
            <div class="hor-thead">
                <span>Dia da Semana</span><span>Abertura</span><span>Fechamento</span><span>Status</span>
            </div>
            <?php foreach ($horarios['consultas'] as $i => $row):
                $st = $row['status'] ?? 'aberto';
                $dc = $st==='aberto'?'#27ae60':($st==='limitado'?'#f39c12':'#e74c3c');
            ?>
            <div class="hor-row">
                <div class="hor-day">
                    <span class="hor-dot" style="background:<?= $dc ?>"></span>
                                        <?= e($row['dia']) ?>
                </div>
                <div class="t-wrap"><i class="fas fa-sun"></i>
                    <input type="time" name="abertura[<?= $i ?>]" value="<?= e($row['abertura']) ?>" <?= $st==='fechado'?'disabled':'' ?>>
                </div>
                <div class="t-wrap"><i class="fas fa-moon"></i>
                    <input type="time" name="fechamento[<?= $i ?>]" value="<?= e($row['fechamento']) ?>" <?= $st==='fechado'?'disabled':'' ?>>
                </div>
                <select name="status[<?= $i ?>]" class="st-sel s-<?= e($st) ?>" onchange="syncRow(this)">
                    <option value="aberto"   <?= $st==='aberto'   ?'selected':'' ?>>✅ Aberto</option>
                    <option value="limitado" <?= $st==='limitado' ?'selected':'' ?>>⚠️ Limitado</option>
                    <option value="fechado"  <?= $st==='fechado'  ?'selected':'' ?>>🔴 Fechado</option>
                </select>
            </div>
            <?php endforeach; ?>
            <div class="hor-foot">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar Horários de Consulta</button>
            </div>
        </form>
    </div>
</div>

<!-- TOSAGEM -->
<div class="panel-card" style="margin-bottom:24px;">
    <div class="panel-card-body">
        <div class="hor-hd">
            <div class="hor-hd-icon" style="background:linear-gradient(135deg,#8e44ad,#6c3483);">
                <i class="fas fa-scissors"></i>
            </div>
            <div>
                <h2>Horários de Banho &amp; Tosa</h2>
                <p>Serviço de estética pet – configure disponibilidade por dia</p>
            </div>
        </div>
        <form method="POST">
            <input type="hidden" name="tipo" value="tosagem">
            <div class="hor-thead" style="background:#f0e6fa;">
                <span style="color:#6c3483;">Dia da Semana</span>
                <span style="color:#6c3483;">Abertura</span>
                <span style="color:#6c3483;">Fechamento</span>
                <span style="color:#6c3483;">Status</span>
            </div>
            <?php foreach ($horarios['tosagem'] as $i => $row):
                $st = $row['status'] ?? 'aberto';
                $dc = $st==='aberto'?'#27ae60':($st==='limitado'?'#f39c12':'#e74c3c');
            ?>
            <div class="hor-row hor-row-purple">
                <div class="hor-day">
                    <span class="hor-dot" style="background:<?= $dc ?>"></span>
                                        <?= e($row['dia']) ?>
                </div>
                <div class="t-wrap"><i class="fas fa-sun"></i>
                    <input type="time" name="abertura[<?= $i ?>]" value="<?= e($row['abertura']) ?>" <?= $st==='fechado'?'disabled':'' ?>>
                </div>
                <div class="t-wrap"><i class="fas fa-moon"></i>
                    <input type="time" name="fechamento[<?= $i ?>]" value="<?= e($row['fechamento']) ?>" <?= $st==='fechado'?'disabled':'' ?>>
                </div>
                <select name="status[<?= $i ?>]" class="st-sel s-<?= e($st) ?>" onchange="syncRow(this)">
                    <option value="aberto"   <?= $st==='aberto'   ?'selected':'' ?>>✅ Aberto</option>
                    <option value="limitado" <?= $st==='limitado' ?'selected':'' ?>>⚠️ Limitado</option>
                    <option value="fechado"  <?= $st==='fechado'  ?'selected':'' ?>>🔴 Fechado</option>
                </select>
            </div>
            <?php endforeach; ?>
            <div class="hor-foot">
                <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg,#8e44ad,#6c3483);box-shadow:0 4px 16px rgba(142,68,173,.35);">
                    <i class="fas fa-save"></i> Salvar Horários de Tosa
                </button>
            </div>
        </form>
    </div>
</div>

<!-- URGENCIA -->
<div class="panel-card">
    <div class="panel-card-body">
        <div class="hor-hd">
            <div class="hor-hd-icon" style="background:linear-gradient(135deg,#e74c3c,#c0392b);">
                <i class="fas fa-ambulance"></i>
            </div>
            <div>
                <h2>Horários de Urgência</h2>
                <p>Atendimento emergencial – períodos disponíveis</p>
            </div>
        </div>
        <form method="POST">
            <input type="hidden" name="tipo" value="urgencia">
            <div class="urg-thead">
                <span>Período</span><span>Abertura</span><span>Fechamento</span>
            </div>
            <?php foreach ($horarios['urgencia'] as $i => $row): ?>
            <div class="urg-row">
                <div class="hor-day">
                    <span class="hor-dot" style="background:#e74c3c;"></span>
                                        <?= e($row['periodo']) ?>
                </div>
                <div class="t-wrap"><i class="fas fa-sun"></i>
                    <input type="time" name="abertura[<?= $i ?>]" value="<?= e($row['abertura']) ?>">
                </div>
                <div class="t-wrap"><i class="fas fa-moon"></i>
                    <input type="time" name="fechamento[<?= $i ?>]" value="<?= e($row['fechamento']) ?>">
                </div>
            </div>
            <?php endforeach; ?>
            <div class="hor-foot">
                <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg,#e74c3c,#c0392b);box-shadow:0 4px 16px rgba(231,76,60,.35);">
                    <i class="fas fa-save"></i> Salvar Horários de Urgência
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function syncRow(sel) {
    const v    = sel.value;
    sel.className = 'st-sel s-' + v;
    const row  = sel.closest('.hor-row, .urg-row');
    const dot  = row?.querySelector('.hor-dot');
    const times = row?.querySelectorAll('input[type="time"]');
    const clr  = {aberto:'#27ae60', limitado:'#f39c12', fechado:'#e74c3c'};
    if (dot)   dot.style.background = clr[v] ?? '#ccc';
    if (times) times.forEach(t => t.disabled = v === 'fechado');
}
</script>

<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>
