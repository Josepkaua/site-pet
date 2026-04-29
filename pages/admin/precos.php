<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
auth_admin();

$adminPage  = 'precos';
$adminTitle = 'Tabela de Precos';
$adminSub   = 'Edite os precos exibidos no site para os clientes';
require_once __DIR__ . '/../../includes/admin_layout.php';

$file   = __DIR__ . '/../../data/precos.json';
$precos = json_decode(file_get_contents($file), true) ?? [];
$success = $error = '';

// Secoes builtin + customizadas
$builtinTipos = ['consultas','tosagem','exames'];

// Se nao tiver _sections, inicializa
if (empty($precos['_sections'])) {
    $precos['_sections'] = [
        ['tipo'=>'consultas','titulo'=>'Consultas & Vacinas', 'icon'=>'stethoscope','color'=>'#2e9e6b','builtin'=>true],
        ['tipo'=>'tosagem',  'titulo'=>'Banho & Tosa',        'icon'=>'scissors',   'color'=>'#8e44ad','builtin'=>true],
        ['tipo'=>'exames',   'titulo'=>'Exames & Procedimentos','icon'=>'flask',     'color'=>'#2980b9','builtin'=>true],
    ];
    file_put_contents($file, json_encode($precos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Salvar itens de uma secao
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'save') {
    $tipo = preg_replace('/[^a-z0-9_]/', '', $_POST['tipo'] ?? '');
    // Verifica que o tipo existe nas secoes
    $tiposValidos = array_column($precos['_sections'], 'tipo');
    if ($tipo && in_array($tipo, $tiposValidos)) {
        $rows = [];
        foreach ($_POST['servico'] ?? [] as $i => $sv) {
            $sv = htmlspecialchars(trim($sv), ENT_QUOTES);
            $pr = number_format((float)str_replace(',', '.', $_POST['preco'][$i] ?? '0'), 2, '.', '');
            if ($sv) $rows[] = ['servico'=>$sv, 'preco'=>$pr];
        }
        $precos[$tipo] = $rows;
        if (file_put_contents($file, json_encode($precos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            $success = 'Precos da secao atualizados!';
            $precos  = json_decode(file_get_contents($file), true);
        } else { $error = 'Erro ao salvar. Verifique permissoes da pasta data/.'; }
    }
}

// Adicionar nova secao
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'add_section') {
    $titulo = htmlspecialchars(trim($_POST['titulo'] ?? ''), ENT_QUOTES);
    $icon   = preg_replace('/[^a-z0-9-]/', '', $_POST['icon']   ?? 'list');
    $color  = preg_match('/^#[0-9a-fA-F]{6}$/', $_POST['color'] ?? '') ? $_POST['color'] : '#555555';
    if ($titulo) {
        // Gera slug unico
        $slug = 'custom_' . time();
        $precos['_sections'][] = ['tipo'=>$slug,'titulo'=>$titulo,'icon'=>$icon,'color'=>$color,'builtin'=>false];
        $precos[$slug] = [];
        if (file_put_contents($file, json_encode($precos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            $success = 'Tabela "' . $titulo . '" criada com sucesso!';
            $precos  = json_decode(file_get_contents($file), true);
        }
    }
}

// Deletar secao customizada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'del_section') {
    $tipo = preg_replace('/[^a-z0-9_]/', '', $_POST['tipo'] ?? '');
    $sec  = null;
    foreach ($precos['_sections'] as $s) {
        if ($s['tipo'] === $tipo) { $sec = $s; break; }
    }
    if ($sec && !($sec['builtin'] ?? false)) {
        $precos['_sections'] = array_values(array_filter($precos['_sections'], fn($s) => $s['tipo'] !== $tipo));
        unset($precos[$tipo]);
        if (file_put_contents($file, json_encode($precos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            $success = 'Tabela removida.';
            $precos  = json_decode(file_get_contents($file), true);
        }
    }
}

$sections = $precos['_sections'] ?? [];
// Ícones disponíveis para selecionar
$iconOptions = [
    'stethoscope'=>'Estetoscopio','scissors'=>'Tesoura','flask'=>'Exames',
    'syringe'=>'Seringa','pills'=>'Medicamento','tooth'=>'Odontologia',
    'paw'=>'Pata','dog'=>'Cachorro','cat'=>'Gato','fish'=>'Peixe',
    'star'=>'Estrela','heart'=>'Coracao','spa'=>'Spa','pump-soap'=>'Higiene',
    'bone'=>'Osso','list'=>'Lista','tag'=>'Preco','dumbbell'=>'Fisioterapia',
];
?>

<?php if ($success): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
<?php endif; ?>

<style>
/* ---- Secoes de Precos ---- */
.preco-section { margin-bottom: 28px; }

.preco-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    border-radius: var(--radius) var(--radius) 0 0;
    color: #fff;
}
.preco-header-left { display: flex; align-items: center; gap: 14px; }
.preco-header-icon {
    width: 44px; height: 44px;
    background: rgba(255,255,255,.2);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem;
    backdrop-filter: blur(4px);
}
.preco-header h2  { font-size: 1.05rem; font-weight: 700; margin: 0; }
.preco-header p   { font-size: .78rem; opacity: .8; margin: 2px 0 0; }
.preco-header-actions { display: flex; gap: 8px; }

.preco-body { background: var(--white); border-radius: 0 0 var(--radius) var(--radius); padding: 0; box-shadow: var(--card-shadow); }

/* Cabeçalho da tabela */
.preco-table-head {
    display: grid;
    grid-template-columns: 40px 1fr 140px 40px;
    gap: 0;
    padding: 10px 20px;
    background: var(--bg2);
}
.preco-table-head span {
    font-size: .7rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .06em;
    color: var(--text-light);
}
.preco-table-head span:nth-child(3) { text-align: right; padding-right: 8px; }

/* Linha de item */
.preco-row {
    display: grid;
    grid-template-columns: 40px 1fr 140px 40px;
    gap: 0;
    align-items: center;
    padding: 8px 20px;
    border-bottom: 1px solid var(--bg2);
    transition: background .15s;
}
.preco-row:last-child { border-bottom: none; }
.preco-row:hover { background: var(--bg); }

.preco-row-num {
    font-size: .72rem; font-weight: 700;
    color: var(--text-light);
    width: 24px; height: 24px;
    background: var(--bg2);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

.preco-input-nome {
    border: 2px solid transparent;
    border-radius: 8px;
    padding: 8px 10px;
    font-size: .88rem;
    font-family: inherit;
    color: var(--text);
    background: transparent;
    transition: all .2s;
    width: 100%;
}
.preco-input-nome:focus {
    outline: none;
    border-color: var(--primary);
    background: var(--bg);
}

.preco-input-valor {
    border: 2px solid transparent;
    border-radius: 8px;
    padding: 8px 10px;
    font-size: .9rem;
    font-weight: 700;
    font-family: inherit;
    color: var(--text);
    background: transparent;
    text-align: right;
    transition: all .2s;
    width: 100%;
}
.preco-input-valor:focus {
    outline: none;
    border-color: var(--primary);
    background: var(--bg);
}

.preco-del-btn {
    width: 30px; height: 30px;
    border-radius: 8px;
    border: none;
    background: transparent;
    color: #ccc;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: .8rem;
    transition: all .2s;
    margin-left: auto;
}
.preco-del-btn:hover { background: #fdedec; color: #e74c3c; }

/* Botao adicionar linha */
.preco-add-row-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 12px 20px;
    border: 2px dashed #e0e0e0;
    border-radius: 0 0 var(--radius) var(--radius);
    background: transparent;
    color: var(--text-light);
    font-size: .85rem;
    font-family: inherit;
    cursor: pointer;
    transition: all .2s;
    text-align: left;
}
.preco-add-row-btn:hover {
    border-color: var(--primary);
    color: var(--primary);
    background: rgba(46,158,107,.04);
}

/* Footer da secao */
.preco-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-top: 1.5px solid var(--bg2);
    background: var(--white);
    border-radius: 0 0 var(--radius) var(--radius);
}
.preco-count { font-size: .8rem; color: var(--text-light); }

/* Modal nova tabela */
.modal-bg {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,.5);
    z-index: 2000;
    align-items: center;
    justify-content: center;
}
.modal-bg.open { display: flex; }
.modal-box {
    background: var(--white);
    border-radius: var(--radius);
    padding: 32px;
    width: 500px;
    max-width: 95vw;
    box-shadow: 0 20px 60px rgba(0,0,0,.25);
    animation: slideUp .3s ease;
}
@keyframes slideUp { from { transform: translateY(30px); opacity: 0; } }
.modal-box h3 { font-size: 1.1rem; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
.modal-field { margin-bottom: 16px; }
.modal-field label { display: block; font-size: .82rem; font-weight: 600; margin-bottom: 6px; }
.modal-field input, .modal-field select {
    width: 100%; padding: 10px 14px;
    border: 2px solid #eee; border-radius: 8px;
    font-size: .9rem; font-family: inherit;
    transition: border-color .2s;
}
.modal-field input:focus, .modal-field select:focus { outline: none; border-color: var(--primary); }
.icon-preview {
    display: inline-flex; align-items: center; justify-content: center;
    width: 42px; height: 42px; border-radius: 10px;
    font-size: 1.2rem; color: #fff; margin-top: 8px;
    transition: background .2s;
}
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 24px; }

@media (max-width: 600px) {
    .preco-table-head, .preco-row { grid-template-columns: 32px 1fr 110px 36px; padding: 8px 12px; }
}
</style>

<!-- MODAL NOVA TABELA -->
<div class="modal-bg" id="modalNovaTabela">
    <div class="modal-box">
        <h3><i class="fas fa-plus-circle" style="color:var(--primary)"></i> Nova Tabela de Precos</h3>
        <form method="POST">
            <input type="hidden" name="acao" value="add_section">
            <div class="modal-field">
                <label>Nome da Tabela *</label>
                <input type="text" name="titulo" required placeholder="Ex: Fisioterapia, Hospedagem, Odontologia...">
            </div>
            <div style="display:grid;grid-template-columns:1fr 120px;gap:16px;">
                <div class="modal-field">
                    <label>Icone</label>
                    <select name="icon" id="iconSelect" onchange="updatePreview()">
                        <?php foreach ($iconOptions as $k=>$v): ?>
                        <option value="<?= $k ?>"><?= $v ?> (fa-<?= $k ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-field">
                    <label>Cor do cabecalho</label>
                    <input type="color" name="color" id="colorPick" value="#e74c3c" oninput="updatePreview()" style="height:42px;padding:2px 6px;cursor:pointer;">
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:12px;margin-top:-8px;margin-bottom:8px;">
                <span class="icon-preview" id="iconPreview" style="background:#e74c3c;">
                    <i class="fas fa-list" id="iconPreviewI"></i>
                </span>
                <span style="font-size:.82rem;color:var(--text-light);">Preview do cabecalho</span>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="closeModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Criar Tabela</button>
            </div>
        </form>
    </div>
</div>

<!-- CABECALHO DA PAGINA -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
    <div>
        <p style="color:var(--text-light);font-size:.88rem;"><?= count($sections) ?> tabela(s) configurada(s)</p>
    </div>
    <button type="button" class="btn btn-primary" onclick="openModal()">
        <i class="fas fa-plus"></i> Nova Tabela
    </button>
</div>

<!-- SECOES -->
<?php
$gradients = [
    '#2e9e6b' => 'linear-gradient(135deg,#2e9e6b,#1e7a50)',
    '#8e44ad' => 'linear-gradient(135deg,#8e44ad,#6c3483)',
    '#2980b9' => 'linear-gradient(135deg,#2980b9,#1a5276)',
    '#e74c3c' => 'linear-gradient(135deg,#e74c3c,#c0392b)',
    '#f39c12' => 'linear-gradient(135deg,#f39c12,#d68910)',
    '#16a085' => 'linear-gradient(135deg,#16a085,#0e6655)',
    '#555555' => 'linear-gradient(135deg,#555,#333)',
];
foreach ($sections as $sec):
    $tipo   = $sec['tipo'];
    $titulo = $sec['titulo'];
    $icon   = $sec['icon'] ?? 'list';
    $color  = $sec['color'] ?? '#555555';
    $builtin= $sec['builtin'] ?? false;
    $rows   = $precos[$tipo] ?? [];
    $grad   = $gradients[$color] ?? "linear-gradient(135deg,{$color},{$color}dd)";
?>
<div class="preco-section">
    <!-- Cabecalho colorido -->
    <div class="preco-header" style="background:<?= $grad ?>;">
        <div class="preco-header-left">
            <div class="preco-header-icon">
                <i class="fas fa-<?= htmlspecialchars($icon) ?>"></i>
            </div>
            <div>
                <h2><?= htmlspecialchars($titulo) ?></h2>
                <p><?= count($rows) ?> item(s) cadastrado(s)</p>
            </div>
        </div>
        <div class="preco-header-actions">
            <?php if (!$builtin): ?>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Remover a tabela &quot;<?= htmlspecialchars($titulo) ?>&quot;? Esta acao nao pode ser desfeita.')">
                <input type="hidden" name="acao" value="del_section">
                <input type="hidden" name="tipo" value="<?= $tipo ?>">
                <button type="submit" class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.4);">
                    <i class="fas fa-trash"></i> Remover
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Corpo com tabela -->
    <div class="preco-body">
        <form method="POST">
            <input type="hidden" name="acao" value="save">
            <input type="hidden" name="tipo" value="<?= $tipo ?>">

            <div class="preco-table-head">
                <span>#</span>
                <span>Servico / Descricao</span>
                <span style="text-align:right;padding-right:8px;">Preco (R$)</span>
                <span></span>
            </div>

            <div id="rows-<?= $tipo ?>">
                <?php foreach ($rows as $i => $r): ?>
                <div class="preco-row">
                    <div class="preco-row-num"><?= $i + 1 ?></div>
                    <input type="text"
                           class="preco-input-nome"
                           name="servico[]"
                           value="<?= htmlspecialchars($r['servico']) ?>"
                           placeholder="Nome do servico"
                           required>
                    <div style="display:flex;align-items:center;gap:4px;">
                        <span style="font-size:.8rem;color:var(--text-light);padding-left:4px;">R$</span>
                        <input type="text"
                               class="preco-input-valor"
                               name="preco[]"
                               value="<?= number_format((float)$r['preco'], 2, ',', '') ?>"
                               placeholder="0,00">
                    </div>
                    <button type="button" class="preco-del-btn" onclick="removeRow(this)" title="Remover">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Botao adicionar linha -->
            <button type="button" class="preco-add-row-btn" onclick="addPrecRow('<?= $tipo ?>')">
                <i class="fas fa-plus-circle"></i>
                Adicionar servico
            </button>

            <!-- Footer com salvar -->
            <div class="preco-footer">
                <span class="preco-count">
                    <i class="fas fa-list" style="color:<?= $color ?>"></i>
                    <span id="count-<?= $tipo ?>"><?= count($rows) ?></span> servico(s)
                </span>
                <button type="submit" class="btn btn-sm btn-primary"
                        style="background:<?= $grad ?>;box-shadow:0 4px 14px <?= $color ?>55;">
                    <i class="fas fa-save"></i> Salvar
                </button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>

<script>
/* ---------- Modal ---------- */
function openModal()  { document.getElementById('modalNovaTabela').classList.add('open'); }
function closeModal() { document.getElementById('modalNovaTabela').classList.remove('open'); }
document.getElementById('modalNovaTabela').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
function updatePreview() {
    const icon  = document.getElementById('iconSelect').value;
    const color = document.getElementById('colorPick').value;
    const prev  = document.getElementById('iconPreview');
    document.getElementById('iconPreviewI').className = 'fas fa-' + icon;
    prev.style.background = color;
}

/* ---------- Adicionar linha ---------- */
function addPrecRow(tipo) {
    const container = document.getElementById('rows-' + tipo);
    const num = container.querySelectorAll('.preco-row').length + 1;
    const div = document.createElement('div');
    div.className = 'preco-row';
    div.innerHTML = `
        <div class="preco-row-num">${num}</div>
        <input type="text"   class="preco-input-nome"  name="servico[]" placeholder="Nome do servico" required autofocus>
        <div style="display:flex;align-items:center;gap:4px;">
            <span style="font-size:.8rem;color:var(--text-light);padding-left:4px;">R$</span>
            <input type="text" class="preco-input-valor" name="preco[]" placeholder="0,00">
        </div>
        <button type="button" class="preco-del-btn" onclick="removeRow(this)" title="Remover">
            <i class="fas fa-times"></i>
        </button>`;
    container.appendChild(div);
    div.querySelector('.preco-input-nome').focus();
    updateCounts(tipo);
}

/* ---------- Remover linha ---------- */
function removeRow(btn) {
    const row     = btn.closest('.preco-row');
    const section = row.closest('form');
    const tipo    = section.querySelector('[name="tipo"]').value;
    row.style.transition = 'opacity .2s, transform .2s';
    row.style.opacity    = '0';
    row.style.transform  = 'translateX(20px)';
    setTimeout(() => {
        row.remove();
        renumberRows(tipo);
        updateCounts(tipo);
    }, 200);
}

function renumberRows(tipo) {
    document.querySelectorAll(`#rows-${tipo} .preco-row-num`).forEach((el, i) => {
        el.textContent = i + 1;
    });
}

function updateCounts(tipo) {
    const n = document.querySelectorAll(`#rows-${tipo} .preco-row`).length;
    const el = document.getElementById('count-' + tipo);
    if (el) el.textContent = n;
}
</script>

<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>
