<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
auth_admin();

$adminPage  = 'produtos';
$adminTitle = 'Produtos da Loja';
$adminSub   = 'Gerencie os produtos exibidos na loja do site';
require_once __DIR__ . '/../../includes/admin_layout.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'add') {
    $obj = [
        'nome'      => htmlspecialchars(trim($_POST['nome']),      ENT_QUOTES),
        'descricao' => htmlspecialchars(trim($_POST['descricao']), ENT_QUOTES),
        'preco'     => (float)str_replace(',', '.', $_POST['preco'] ?? '0'),
        'categoria' => htmlspecialchars(trim($_POST['categoria']), ENT_QUOTES),
        'emoji'     => htmlspecialchars(trim($_POST['emoji']),     ENT_QUOTES),
        'estoque'   => (int)($_POST['estoque'] ?? 0),
        'ativo'     => true,
    ];
    $r  = gql('mutation($obj:produtos_insert_input!){insert_produtos_one(object:$obj){id}}', ['obj'=>$obj]);
    $msg = $r['ok'] ? 'success|Produto adicionado!' : 'error|Erro ao adicionar. Verifique o Nhost.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'toggle') {
    $id    = (int)$_POST['id'];
    $ativo = $_POST['ativo'] === '1' ? false : true;
    $r = gql('mutation($id:bigint!,$a:Boolean!){update_produtos_by_pk(pk_columns:{id:$id},_set:{ativo:$a}){id}}',
             ['id'=>$id,'a'=>$ativo]);
    $msg = $r['ok'] ? 'success|Produto atualizado!' : 'error|Erro ao atualizar.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'estoque') {
    $id = (int)$_POST['id'];
    if (isset($_POST['valor'])) {
        $val = max(0, (int)$_POST['valor']);
        $r = gql('mutation($id:bigint!,$e:Int!){update_produtos_by_pk(pk_columns:{id:$id},_set:{estoque:$e}){id}}',
                 ['id'=>$id,'e'=>$val]);
    } else {
        $delta = (int)($_POST['delta'] ?? 0);
        $r = gql('mutation($id:bigint!,$d:Int!){update_produtos_by_pk(pk_columns:{id:$id},_inc:{estoque:$d}){id}}',
                 ['id'=>$id,'d'=>$delta]);
    }
    $msg = $r['ok'] ? 'success|Estoque atualizado!' : 'error|Erro ao atualizar estoque.';
}

$q     = gql('query { produtos(order_by:{categoria:asc,nome:asc}) { id nome descricao preco categoria emoji estoque ativo } }');
$prods = $q['data']['produtos'] ?? [];
$cats  = ['racao'=>'Racao','petisco'=>'Petisco','higiene'=>'Higiene','saude'=>'Saude','acessorio'=>'Acessorio','brinquedo'=>'Brinquedo'];
?>

<?php if ($msg): [$t,$txt] = explode('|',$msg,2); ?>
<div class="alert alert-<?= $t==='success'?'success':'error' ?>"><i class="fas fa-check-circle"></i> <?= $txt ?></div>
<?php endif; ?>

<div class="panel-card" style="margin-bottom:24px;">
    <div class="panel-card-header"><h2><i class="fas fa-plus"></i> Adicionar Produto</h2></div>
    <div class="panel-card-body">
        <form method="POST">
            <input type="hidden" name="acao" value="add">
            <div class="form-row">
                <div class="field-group"><label>Nome *</label><input type="text" name="nome" required placeholder="Ex: Racao Premium 15kg"></div>
                <div class="field-group"><label>Emoji</label><input type="text" name="emoji" placeholder="🥩" maxlength="4"></div>
            </div>
            <div class="form-row">
                <div class="field-group">
                    <label>Categoria</label>
                    <select name="categoria">
                        <?php foreach ($cats as $v=>$l): ?><option value="<?= $v ?>"><?= $l ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="field-group"><label>Preco (R$) *</label><input type="text" name="preco" required placeholder="0,00"></div>
                <div class="field-group"><label>Estoque inicial</label><input type="number" name="estoque" value="0" min="0"></div>
            </div>
            <div class="form-row full">
                <div class="field-group"><label>Descricao</label><input type="text" name="descricao" placeholder="Breve descricao"></div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Adicionar Produto</button>
        </form>
    </div>
</div>

<div class="panel-card">
    <div class="panel-card-header">
        <h2><i class="fas fa-list"></i> Produtos Cadastrados</h2>
        <span style="font-size:.85rem;color:var(--text-light);"><?= count($prods) ?> produto(s)</span>
    </div>
    <div class="panel-card-body" style="padding:0;overflow-x:auto;">
        <?php if (empty($prods)): ?>
            <p style="padding:32px;text-align:center;color:var(--text-light);">
                <?= !$q['ok'] ? '<i class="fas fa-plug"></i> Nhost nao configurado.' : 'Nenhum produto cadastrado.' ?>
            </p>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr><th>Produto</th><th>Categoria</th><th>Preco</th><th style="min-width:200px;">Estoque</th><th>Status</th><th>Acao</th></tr>
            </thead>
            <tbody>
            <?php foreach ($prods as $p): ?>
            <tr style="<?= !$p['ativo'] ? 'opacity:.45' : '' ?>">
                <td>
                    <span style="font-size:1.3rem;margin-right:6px;"><?= $p['emoji'] ?></span>
                    <strong style="font-size:.9rem;"><?= htmlspecialchars($p['nome']) ?></strong>
                    <br><small style="color:var(--text-light);"><?= htmlspecialchars($p['descricao']) ?></small>
                </td>
                <td><span class="badge badge-confirmado"><?= $cats[$p['categoria']] ?? $p['categoria'] ?></span></td>
                <td style="font-weight:600;color:var(--primary);">R$ <?= number_format($p['preco'],2,',','.') ?></td>

                <td>
                    <div class="estoque-ctrl">
                        <!-- Botao -1 -->
                        <form method="POST" style="display:contents;">
                            <input type="hidden" name="acao"  value="estoque">
                            <input type="hidden" name="id"    value="<?= $p['id'] ?>">
                            <input type="hidden" name="delta" value="-1">
                            <button type="submit" class="estq-btn estq-minus" <?= $p['estoque']<=0?'disabled':'' ?> title="-1">
                                <i class="fas fa-minus"></i>
                            </button>
                        </form>

                        <!-- Input valor absoluto -->
                        <form method="POST" style="display:contents;">
                            <input type="hidden" name="acao" value="estoque">
                            <input type="hidden" name="id"   value="<?= $p['id'] ?>">
                            <input type="number" name="valor" value="<?= $p['estoque'] ?>" min="0"
                                   class="estq-input" title="Edite e pressione Enter"
                                   onchange="this.form.submit()">
                        </form>

                        <!-- Botao +1 -->
                        <form method="POST" style="display:contents;">
                            <input type="hidden" name="acao"  value="estoque">
                            <input type="hidden" name="id"    value="<?= $p['id'] ?>">
                            <input type="hidden" name="delta" value="1">
                            <button type="submit" class="estq-btn estq-plus" title="+1">
                                <i class="fas fa-plus"></i>
                            </button>
                        </form>

                        <!-- Badge de nivel -->
                        <span class="estq-badge <?= $p['estoque']<=0?'estq-zero':($p['estoque']<=5?'estq-low':($p['estoque']<=20?'estq-med':'estq-ok')) ?>">
                            <?php if ($p['estoque'] <= 0): ?>
                                <i class="fas fa-times-circle"></i> Zerado
                            <?php elseif ($p['estoque'] <= 5): ?>
                                <i class="fas fa-exclamation-triangle"></i> Baixo
                            <?php elseif ($p['estoque'] <= 20): ?>
                                <i class="fas fa-exclamation-circle"></i> Medio
                            <?php else: ?>
                                <i class="fas fa-check-circle"></i> OK
                            <?php endif; ?>
                        </span>
                    </div>
                </td>

                <td><span class="badge <?= $p['ativo']?'badge-confirmado':'badge-cancelado' ?>"><?= $p['ativo']?'Ativo':'Inativo' ?></span></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="acao"  value="toggle">
                        <input type="hidden" name="id"    value="<?= $p['id'] ?>">
                        <input type="hidden" name="ativo" value="<?= $p['ativo']?'1':'0' ?>">
                        <button type="submit" class="btn <?= $p['ativo']?'btn-warning':'btn-success' ?> btn-sm">
                            <i class="fas fa-<?= $p['ativo']?'eye-slash':'eye' ?>"></i>
                            <?= $p['ativo']?'Desativar':'Ativar' ?>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<style>
.estoque-ctrl,.estq-ctrl{display:flex;align-items:center;gap:6px;}
.estq-btn{width:30px;height:30px;border-radius:50%;border:2px solid;background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.68rem;transition:all .2s;flex-shrink:0;font-family:inherit;}
.estq-plus{border-color:var(--primary);color:var(--primary);}
.estq-plus:hover{background:var(--primary);color:#fff;}
.estq-minus{border-color:#e74c3c;color:#e74c3c;}
.estq-minus:hover:not(:disabled){background:#e74c3c;color:#fff;}
.estq-btn:disabled{opacity:.3;cursor:not-allowed;}
.estq-input{width:58px;text-align:center;border:2px solid #e0e0e0;border-radius:8px;padding:5px 4px;font-size:.88rem;font-weight:700;font-family:inherit;color:var(--text);}
.estq-input:focus{outline:none;border-color:var(--primary);}
.estq-badge{font-size:.7rem;font-weight:600;padding:3px 9px;border-radius:50px;white-space:nowrap;}
.estq-ok  {background:#d5f5e3;color:#1e8449;}
.estq-med {background:#fef9e7;color:#9a7d0a;}
.estq-low {background:#fde8d8;color:#ba4a00;}
.estq-zero{background:#fadbd8;color:#922b21;}
</style>

<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>
