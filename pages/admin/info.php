<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
auth_admin();

$adminPage  = 'info';
$adminTitle = 'Dados da Clínica';
$adminSub   = 'Altere telefone, endereço, redes sociais e outras informações';
require_once __DIR__ . '/../../includes/admin_layout.php';

$infoFile = __DIR__ . '/../../data/info.json';
$info = file_exists($infoFile) ? json_decode(file_get_contents($infoFile), true) : [];
$defaults = [
    'nome'        => CLINIC_NAME,
    'dra'         => CLINIC_DRA,
    'crmv'        => CLINIC_CRMV,
    'telefone'    => CLINIC_PHONE,
    'whatsapp'    => CLINIC_WHATS,
    'email'       => CLINIC_EMAIL,
    'endereco'    => CLINIC_ADDRESS,
    'instagram'   => 'https://instagram.com',
    'facebook'    => 'https://facebook.com',
    'sobre'       => 'Com mais de 10 anos de experiência no atendimento de pequenos animais, Dra. Milena Paiva é apaixonada pelo que faz.',
];
$info = array_merge($defaults, $info);

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $campos = ['nome','dra','crmv','telefone','whatsapp','email','endereco','instagram','facebook','sobre'];
    $novo = [];
    foreach ($campos as $c) {
        $novo[$c] = htmlspecialchars(trim($_POST[$c] ?? ''), ENT_QUOTES);
    }
    if (file_put_contents($infoFile, json_encode($novo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        $success = 'Dados salvos! Para refletir no site, atualize as constantes em includes/config.php também.';
        $info = $novo;
    } else {
        $error = 'Erro ao salvar. Verifique permissões da pasta data/.';
    }
}
?>

<?php  if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php  endif; ?>
<?php  if ($error):   ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php  endif; ?>

<div class="panel-card">
    <div class="panel-card-header"><h2><i class="fas fa-clinic-medical"></i> Informações da Clínica</h2></div>
    <div class="panel-card-body">
        <form method="POST">
            <div class="form-row">
                <div class="field-group">
                    <label>Nome da Clínica</label>
                    <input type="text" name="nome" value="<?= htmlspecialchars($info['nome']) ?>">
                </div>
                <div class="field-group">
                    <label>Nome da Veterinária</label>
                    <input type="text" name="dra" value="<?= htmlspecialchars($info['dra']) ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="field-group">
                    <label>CRMV</label>
                    <input type="text" name="crmv" value="<?= htmlspecialchars($info['crmv']) ?>">
                </div>
                <div class="field-group">
                    <label>Telefone</label>
                    <input type="tel" name="telefone" value="<?= htmlspecialchars($info['telefone']) ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="field-group">
                    <label>WhatsApp (somente números, com DDD)</label>
                    <input type="text" name="whatsapp" value="<?= htmlspecialchars($info['whatsapp']) ?>" placeholder="5511999999999">
                </div>
                <div class="field-group">
                    <label>E-mail</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($info['email']) ?>">
                </div>
            </div>
            <div class="form-row full">
                <div class="field-group">
                    <label>Endereço</label>
                    <input type="text" name="endereco" value="<?= htmlspecialchars($info['endereco']) ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="field-group">
                    <label>Instagram (URL completa)</label>
                    <input type="url" name="instagram" value="<?= htmlspecialchars($info['instagram']) ?>">
                </div>
                <div class="field-group">
                    <label>Facebook (URL completa)</label>
                    <input type="url" name="facebook" value="<?= htmlspecialchars($info['facebook']) ?>">
                </div>
            </div>
            <div class="form-row full">
                <div class="field-group">
                    <label>Texto "Sobre" (aparece na página inicial)</label>
                    <textarea name="sobre" rows="4"><?= htmlspecialchars($info['sobre']) ?></textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar Dados</button>
        </form>
    </div>
</div>

<?php  require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>
