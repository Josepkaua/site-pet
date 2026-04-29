<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
auth_admin();

$adminPage  = 'agendamentos';
$adminTitle = 'Consultas Agendadas';
$adminSub   = 'Gerencie todos os agendamentos de consulta';
require_once __DIR__ . '/../../includes/admin_layout.php';

$msg = '';

// Atualizar status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['status'])) {
    $id     = (int)$_POST['id'];
    $status = in_array($_POST['status'], ['pendente','confirmado','cancelado','concluido']) ? $_POST['status'] : 'pendente';
    $r = gql('mutation($id:bigint!,$s:String!){update_agendamentos_by_pk(pk_columns:{id:$id},_set:{status:$s}){id}}',
             ['id'=>$id,'s'=>$status]);
    $msg = $r['ok'] ? 'success|Status atualizado!' : 'error|Erro ao atualizar.';
}

$filtro = $_GET['status'] ?? 'todos';
$where  = $filtro !== 'todos' ? ',where:{status:{_eq:"'.$filtro.'"}}' : '';

$q = gql('query { agendamentos(order_by:{data:asc,horario:asc}'.$where.') {
    id nome telefone email pet_nome pet_especie servico data horario obs status criado_em } }');
$agendamentos = array_filter($q['data']['agendamentos'] ?? [], 'is_array');

$statusList = ['todos'=>'Todos','pendente'=>'Pendentes','confirmado'=>'Confirmados','cancelado'=>'Cancelados','concluido'=>'Concluídos'];
?>

<?php  if ($msg): [$tipo,$texto] = explode('|',$msg,2); ?>
<div class="alert alert-<?= $tipo === 'success' ? 'success' : 'error' ?>">
    <i class="fas fa-<?= $tipo === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i> <?= $texto ?>
</div>
<?php  endif; ?>

<!-- FILTROS -->
<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;">
    <?php  foreach ($statusList as $v => $l): ?>
    <a href="?status=<?= $v ?>" class="btn <?= $filtro===$v ? 'btn-primary' : 'btn-outline' ?> btn-sm"><?= $l ?></a>
    <?php  endforeach; ?>
    <span style="margin-left:auto;font-size:.85rem;color:var(--text-light);align-self:center;">
        <?= count($agendamentos) ?> registro(s)
    </span>
</div>

<div class="panel-card">
    <div class="panel-card-body" style="padding:0;overflow-x:auto;">
        <?php  if (empty($agendamentos)): ?>
            <p style="padding:32px;text-align:center;color:var(--text-light);">
                <?php  if (!$q['ok']): ?>
                    <i class="fas fa-plug"></i> Nhost não configurado. Insira o Admin Secret em <code>includes/config.php</code>.
                <?php  else: ?>
                    Nenhum agendamento encontrado.
                <?php  endif; ?>
            </p>
        <?php  else: ?>
        <table class="data-table">
            <thead>
                <tr><th>#</th><th>Data / Hora</th><th>Tutor</th><th>Pet</th><th>Serviço</th><th>Contato</th><th>Status</th><th>Ação</th></tr>
            </thead>
            <tbody>
            <?php  foreach ($agendamentos as $a): ?>
            <tr>
                <td style="color:var(--text-light);">#<?= $a['id'] ?? '–' ?></td>
                <td><?= $a['data'] ? date('d/m/Y', strtotime($a['data'])) : '–' ?><br><small style="color:var(--text-light);"><?= $a['horario'] ?? '–' ?></small></td>
                <td><?= htmlspecialchars($a['nome'] ?? '') ?></td>
                <td><?= htmlspecialchars($a['pet_nome'] ?? '') ?><br><small style="color:var(--text-light);"><?= htmlspecialchars($a['pet_especie'] ?? '') ?></small></td>
                <td style="font-size:.82rem;"><?= htmlspecialchars($a['servico'] ?? '') ?></td>
                <td style="font-size:.8rem;">
                    <?= htmlspecialchars($a['telefone'] ?? '') ?><br>
                    <?php  if (!empty($a['telefone'])): ?>
                    <a href="https://wa.me/55<?= preg_replace('/\D/', '', $a['telefone'] ?? '') ?>" target="_blank" style="color:#25d366;">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                    <?php  endif; ?>
                </td>
                <td><span class="badge badge-<?= $a['status'] ?? 'pendente' ?>"><?= ucfirst($a['status'] ?? 'pendente') ?></span></td>
                <td>
                    <form method="POST" style="display:flex;gap:6px;align-items:center;">
                        <input type="hidden" name="id" value="<?= $a['id'] ?? '' ?>">
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
