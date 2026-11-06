<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
auth_admin();

$adminPage  = 'info';
$adminTitle = 'Dados da Clínica';
$adminSub   = 'Altere telefone, endereço, redes sociais e outras informações';
require_once __DIR__ . '/../../includes/admin_layout.php';

// Valores atuais (config.php já aplica data/info.json sobre os padrões)
$info = [
    'nome'      => CLINIC_NAME,      'dra'       => CLINIC_DRA,
    'crmv'      => CLINIC_CRMV,      'telefone'  => CLINIC_PHONE,
    'whatsapp'  => CLINIC_WHATS,     'email'     => CLINIC_EMAIL,
    'endereco'  => CLINIC_ADDRESS,   'instagram' => CLINIC_INSTAGRAM,
    'facebook'  => CLINIC_FACEBOOK,  'sobre'     => CLINIC_ABOUT,
];

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novo = [
        'nome'      => clean_text($_POST['nome'] ?? '', 120),
        'dra'       => clean_text($_POST['dra'] ?? '', 120),
        'crmv'      => clean_text($_POST['crmv'] ?? '', 40),
        'telefone'  => clean_phone($_POST['telefone'] ?? ''),
        'whatsapp'  => preg_replace('/\D/', '', (string)($_POST['whatsapp'] ?? '')),
        'email'     => clean_email($_POST['email'] ?? ''),
        'endereco'  => clean_text($_POST['endereco'] ?? '', 200),
        'instagram' => clean_text($_POST['instagram'] ?? '', 200),
        'facebook'  => clean_text($_POST['facebook'] ?? '', 200),
        'sobre'     => clean_text($_POST['sobre'] ?? '', 1000, true),
    ];
    $erros = [];
    if (!$novo['nome'])                                   $erros[] = 'nome da clínica';
    if (!$novo['telefone'])                               $erros[] = 'telefone';
    if (strlen($novo['whatsapp']) < 10 || strlen($novo['whatsapp']) > 15) $erros[] = 'WhatsApp (só números, com DDI e DDD, ex: 5511999999999)';
    if (!$novo['email'])                                  $erros[] = 'e-mail';
    foreach (['instagram', 'facebook'] as $rede) {
        if ($novo[$rede] !== '' && !preg_match('#^https://#i', $novo[$rede])) $erros[] = $rede . ' (deve começar com https://)';
    }

    if ($erros) {
        $error = 'Verifique: ' . implode(', ', $erros) . '.';
        $info  = array_merge($info, $novo);
    } elseif (save_json('info', $novo)) {
        $success = 'Dados salvos! O site já mostra as novas informações.';
        $info = $novo;
    } else {
        $error = 'Erro ao salvar. Verifique permissões da pasta data/.';
    }
}
?>

<?php  if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= e($success) ?></div><?php  endif; ?>
<?php  if ($error):   ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div><?php  endif; ?>

<div class="panel-card">
    <div class="panel-card-header"><h2><i class="fas fa-clinic-medical"></i> Informações da Clínica</h2></div>
    <div class="panel-card-body">
        <form method="POST">
            <div class="form-row">
                <div class="field-group">
                    <label>Nome da Clínica</label>
                    <input type="text" name="nome" value="<?= e($info['nome']) ?>">
                </div>
                <div class="field-group">
                    <label>Nome da Veterinária</label>
                    <input type="text" name="dra" value="<?= e($info['dra']) ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="field-group">
                    <label>CRMV</label>
                    <input type="text" name="crmv" value="<?= e($info['crmv']) ?>">
                </div>
                <div class="field-group">
                    <label>Telefone</label>
                    <input type="tel" name="telefone" value="<?= e($info['telefone']) ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="field-group">
                    <label>WhatsApp (somente números, com DDD)</label>
                    <input type="text" name="whatsapp" value="<?= e($info['whatsapp']) ?>" placeholder="5511999999999">
                </div>
                <div class="field-group">
                    <label>E-mail</label>
                    <input type="email" name="email" value="<?= e($info['email']) ?>">
                </div>
            </div>
            <div class="form-row full">
                <div class="field-group">
                    <label>Endereço</label>
                    <input type="text" name="endereco" value="<?= e($info['endereco']) ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="field-group">
                    <label>Instagram (URL completa)</label>
                    <input type="url" name="instagram" value="<?= e($info['instagram']) ?>">
                </div>
                <div class="field-group">
                    <label>Facebook (URL completa)</label>
                    <input type="url" name="facebook" value="<?= e($info['facebook']) ?>">
                </div>
            </div>
            <div class="form-row full">
                <div class="field-group">
                    <label>Texto "Sobre" (aparece na página inicial)</label>
                    <textarea name="sobre" rows="4"><?= e($info['sobre']) ?></textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar Dados</button>
        </form>
    </div>
</div>

<?php  require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>
