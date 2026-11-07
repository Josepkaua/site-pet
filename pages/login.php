<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';

if (is_logged()) {
    header('Location: ' . (is_admin() ? '/pages/admin/index.php' : '/pages/painel/index.php'));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean_text($_POST['email'] ?? '', 160);
    $pass  = (string)($_POST['password'] ?? '');

    if (!rate_limit('login', 5, 900, false)) {
        $error = 'Muitas tentativas. Aguarde 15 minutos e tente novamente.';
    } elseif (auth_login($email, $pass)) {
        rate_limit_reset('login');
        header('Location: ' . (is_admin() ? '/pages/admin/index.php' : '/pages/painel/index.php'));
        exit;
    } else {
        rate_limit('login', 5, 900);       // conta a tentativa falha
        usleep(random_int(200000, 500000));
        $error = 'E-mail ou senha incorretos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Entrar – <?= e(CLINIC_NAME) ?></title>
    <link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css?v=3">
</head>
<body class="login-page">
<div class="login-bg" aria-hidden="true">
    <i class="fas fa-paw"></i><i class="fas fa-paw"></i><i class="fas fa-paw"></i><i class="fas fa-paw"></i>
</div>
<main class="login-wrap">
    <div class="login-card anim-up">
        <div class="login-logo">
            <span class="login-logo-icon"><i class="fas fa-paw"></i></span>
            <h1><?= e(CLINIC_NAME) ?></h1>
            <p>Área restrita – faça seu login</p>
        </div>

        <?php if ($error): ?>
            <div class="login-error" role="alert"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="js-submit-loading" autocomplete="on">
            <div class="login-field">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Seu e-mail" required maxlength="160" autocomplete="username"
                       aria-label="E-mail" value="<?= old('email') ?>">
            </div>
            <div class="login-field">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="loginPass" placeholder="Senha" required maxlength="200"
                       autocomplete="current-password" aria-label="Senha">
                <button type="button" class="login-eye" data-toggle-pass="loginPass" aria-label="Mostrar senha"><i class="fas fa-eye"></i></button>
            </div>
            <button type="submit" class="login-submit">
                <i class="fas fa-sign-in-alt"></i> Entrar
            </button>
        </form>

        <span class="login-back">
            <a href="/index.php"><i class="fas fa-arrow-left"></i> Voltar ao site</a>
        </span>
    </div>
</main>
<script src="/assets/js/main.js?v=3"></script>
</body>
</html>
