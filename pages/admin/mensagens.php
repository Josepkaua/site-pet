<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
auth_admin();

$adminPage  = 'mensagens';
$adminTitle = 'Mensagens de Contato';
$adminSub   = 'Mensagens enviadas pelos clientes pelo formulário de contato';
require_once __DIR__ . '/../../includes/admin_layout.php';

// Marcar como lida
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    gql('mutation($id:bigint!){update_contatos_by_pk(pk_columns:{id:$id},_set:{lido:true}){id}}', ['id'=>(int)$_POST['id']]);
}

$filtro = $_GET['filtro'] ?? 'nao_lidas';
$where  = $filtro === 'nao_lidas' ? ',where:{lido:{_eq:false}}' : '';

$q = gql('query { contatos(order_by:{criado_em:desc}'.$where.') { id nome email assunto mensagem lido criado_em } }');
$msgs = $q['data']['contatos'] ?? [];
?>

<div style="display:flex;gap:8px;margin-bottom:20px;">
    <a href="?filtro=nao_lidas" class="btn <?= $filtro==='nao_lidas'?'btn-primary':'btn-outline' ?> btn-sm">Não lidas</a>
    <a href="?filtro=todas"     class="btn <?= $filtro==='todas'    ?'btn-primary':'btn-outline' ?> btn-sm">Todas</a>
</div>

<?php  if (empty($msgs)): ?>
<div class="panel-card">
    <div class="panel-card-body" style="text-align:center;padding:48px;color:var(--text-light);">
        <?= !$q['ok'] ? '<i class="fas fa-plug" style="font-size:2rem;"></i><br>Nhost não configurado. Insira o Admin Secret em config.php.' : '<i class="fas fa-check-circle" style="font-size:2rem;color:var(--success)"></i><br>Nenhuma mensagem não lida!' ?>
    </div>
</div>
<?php  else: ?>
<div style="display:flex;flex-direction:column;gap:16px;">
    <?php  foreach ($msgs as $m): ?>
    <div class="panel-card" style="<?= !$m['lido'] ? 'border-left:4px solid var(--primary)' : '' ?>">
        <div class="panel-card-body">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:12px;">
                <div>
                    <strong style="font-size:1rem;"><?= htmlspecialchars($m['nome']) ?></strong>
                    <?php  if (!$m['lido']): ?><span class="badge badge-pendente" style="margin-left:8px;">Nova</span><?php  endif; ?>
                    <br>
                    <span style="font-size:.82rem;color:var(--text-light);">
                        <i class="fas fa-envelope"></i> <?= htmlspecialchars($m['email']) ?>
                        &nbsp;|&nbsp;
                        <i class="fas fa-tag"></i> <?= htmlspecialchars($m['assunto'] ?: 'Sem assunto') ?>
                        &nbsp;|&nbsp;
                        <i class="fas fa-clock"></i> <?= date('d/m/Y H:i', strtotime($m['criado_em'])) ?>
                    </span>
                </div>
                <div style="display:flex;gap:8px;flex-shrink:0;">
                    <a href="mailto:<?= htmlspecialchars($m['email']) ?>" class="btn btn-outline btn-sm">
                        <i class="fas fa-reply"></i> Responder
                    </a>
                    <?php  if (!$m['lido']): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $m['id'] ?>">
                        <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Marcar lida</button>
                    </form>
                    <?php  endif; ?>
                </div>
            </div>
            <div style="background:var(--bg);padding:16px;border-radius:8px;font-size:.9rem;color:var(--text);line-height:1.6;">
                <?= nl2br(htmlspecialchars($m['mensagem'])) ?>
            </div>
        </div>
    </div>
    <?php  endforeach; ?>
</div>
<?php  endif; ?>

<?php  require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>
