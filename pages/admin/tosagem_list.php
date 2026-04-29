<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
auth_admin();

$adminPage  = 'tosagem';
$adminTitle = 'Banho & Tosa – Agendamentos';
$adminSub   = 'Gerencie todos os agendamentos de banho e tosa';
require_once __DIR__ . '/../../includes/admin_layout.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['status'])) {
    $id     = (int)$_POST['id'];
    $status = in_array($_POST['status'], ['pendente','confirmado','cancelado','concluido']) ? $_POST['status'] : 'pendente';
    $r = gql('mutation($id:bigint!,$s:String!){update_tosagem_agendamentos_by_pk(pk_columns:{id:$id},_set:{status:$s}){id}}',
             ['id'=>$id,'s'=>$status]);
    $msg = $r['ok'] ? 'success|Status atualizado!' : 'error|Erro ao atualizar.';
}

$filtro = $_GET['status'] ?? 'todos';
$where  = $filtro !== 'todos' ? ',where:{status:{_eq:"'.$filtro.'"}}' : '';

$q = gql('query { tosagem_agendamentos(order_by:{data:asc}'.$where.') {
    id nome telefone pet_nome pet_raca pet_porte servico data horario status criado_em } }');
$lista = $q['data']['tosagem_agendamentos'] ?? [];
?>

<?php  if ($msg): [$tipo,$texto] = explode('|',$msg,2); ?>
<div class="alert alert-<?= $tipo==='success'?'success':'error' ?>"><i class="fas fa-check-circle"></i> <?= $texto ?></div>
<?php  endif; ?>

<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;">
    <?php  foreach (['todos'=>'Todos','pendente'=>'Pendentes','confirmado'=>'Confirmados','cancelado'=>'Cancelados','concluido'=>'Concluídos'] as $v=>$l): ?>
    <a href="?status=<?= $v ?>" class="btn <?= $filtro===$v?'btn-primary':'btn-outline' ?> btn-sm"><?= $l ?></a>
    <?php  endforeach; ?>
</div>

<div class="panel-card">
    <div class="panel-card-body" style="padding:0;overflow-x:auto;">
        <?php  if (empty($lista)): ?>
            <p style="padding:32px;text-align:center;color:var(--text-light);">
                <?= !$q['ok'] ? '<i class="fas fa-plug"></i> Nhost não configurado.' : 'Nenhum agendamento encontrado.' ?>
            </p>
        <?php  else: ?>
        <table class="data-table">
            <thead><tr><th>#</th><th>Data</th><th>Tutor</th><th>Pet</th><th>Porte</th><th>Serviço</th><th>WhatsApp</th><th>Status</th><th>Ação</th></tr></thead>
            <tbody>
            <?php  foreach ($lista as $a): ?>
            <tr>
                <td style="color:var(--text-light);">#<?= $a['id'] ?></td>
                <td><?= date('d/m/Y', strtotime($a['data'])) ?><br><small><?= $a['horario'] ?: '–' ?></small></td>
                <td><?= htmlspecialchars($a['nome']) ?></td>
                <td><?= htmlspecialchars($a['pet_nome']) ?><br><small><?= htmlspecialchars($a['pet_raca'] ?: '') ?></small></td>
                <td style="font-size:.82rem;"><?= htmlspecialchars($a['pet_porte'] ?: '–') ?></td>
                <td style="font-size:.82rem;"><?= htmlspecialchars($a['servico']) ?></td>
                <td>
                    <a href="https://wa.me/55<?= preg_replace('/\D/','',$a['telefone']) ?>" target="_blank" style="color:#25d366;font-size:.82rem;">
                        <i class="fab fa-whatsapp"></i> <?= htmlspecialchars($a['telefone']) ?>
                    </a>
                </td>
                <td><span class="badge badge-<?= $a['status'] ?>"><?= ucfirst($a['status']) ?></span></td>
                <td>
                    <form method="POST" style="display:flex;gap:6px;">
                        <input type="hidden" name="id" value="<?= $a['id'] ?>">
                        <select name="status" style="font-size:.78rem;padding:4px 6px;border-radius:6px;border:1.5px solid #ddd;">
                            <option value="pendente"   <?= $a['status']==='pendente'   ?'selected':'' ?>>Pendente</option>
                            <option value="confirmado" <?= $a['status']==='confirmado' ?'selected':'' ?>>Confirmado</option>
                            <option value="cancelado"  <?= $a['status']==='cancelado'  ?'selected':'' ?>>Cancelado</option>
                            <option value="concluido"  <?= $a['status']==='concluido'  ?'selected':'' ?>>Concluído</option>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-check"></i></button>
                    </form>
                </td>
            </tr>
            <?php  endforeach; ?>
            </tbody>
        </table>
        <?php  endif; ?>
    </div>
</div>

<?php  require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>
