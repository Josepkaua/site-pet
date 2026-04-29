<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

if (is_logged()) {
    header('Location: ' . (is_admin() ? '/pages/admin/index.php' : '/pages/painel/index.php'));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = trim($_POST['password'] ?? '');
    if (auth_login($email, $pass)) {
        header('Location: ' . (is_admin() ? '/pages/admin/index.php' : '/pages/painel/index.php'));
        exit;
    }
    $error = 'E-mail ou senha incorretos.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – <?= CLINIC_NAME ?></title>
    <link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center;
               background: linear-gradient(135deg, #e8f8f0, #d0f0e4); }
        .login-wrap { width: 100%; max-width: 440px; padding: 24px; }
        .login-card { background: #fff; border-radius: 20px; padding: 48px 40px;
                      box-shadow: 0 20px 60px rgba(46,158,107,.15); }
        .login-logo { text-align: center; margin-bottom: 32px; }
        .login-logo i { font-size: 3rem; color: var(--primary); }
        .login-logo h1 { font-size: 1.25rem; font-weight: 700; color: var(--text); margin-top: 10px; }
        .login-logo p  { font-size: .85rem; color: var(--text-light); }
        .login-field   { position: relative; margin-bottom: 18px; }
        .login-field i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
                         color: var(--text-light); }
        .login-field input {
            width: 100%; padding: 14px 16px 14px 44px;
            border: 2px solid #e8e8e8; border-radius: 12px;
            font-family: inherit; font-size: .95rem; color: var(--text);
            transition: border-color .2s; background: var(--bg);
        }
        .login-field input:focus { outline: none; border-color: var(--primary); }
        .login-submit { width: 100%; padding: 14px; background: var(--primary);
                        color: #fff; border: none; border-radius: 12px; font-size: 1rem;
                        font-weight: 600; cursor: pointer; transition: all .25s; font-family: inherit; }
        .login-submit:hover { background: var(--primary-dk); transform: translateY(-2px); }
        .login-error { background: #fadbd8; color: #922b21; padding: 12px 16px;
                       border-radius: 10px; font-size: .88rem; margin-bottom: 18px;
                       display: flex; align-items: center; gap: 8px; }
        .login-hint { background: var(--bg2); border-radius: 12px; padding: 16px;
                      margin-top: 24px; font-size: .82rem; color: var(--text-light); }
        .login-hint strong { color: var(--text); display: block; margin-bottom: 8px; }
        .login-hint code { background: #fff; padding: 2px 6px; border-radius: 4px;
                           font-size: .78rem; color: var(--primary); }
        .login-back { display: block; text-align: center; margin-top: 20px;
                      font-size: .85rem; color: var(--text-light); }
        .login-back a { color: var(--primary); font-weight: 600; }
    </style>
</head>
<body>
<div class="login-wrap">
    <div class="login-card">
        <div class="login-logo">
            <i class="fas fa-paw"></i>
            <h1><?= CLINIC_NAME ?></h1>
            <p>Área restrita – faça seu login</p>
        </div>

        <?php if ($error): ?>
            <div class="login-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="login-field">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Seu e-mail" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="login-field">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Senha" required>
            </div>
            <button type="submit" class="login-submit">
                <i class="fas fa-sign-in-alt"></i> Entrar
            </button>
        </form>

        <div class="login-hint">
            <strong><i class="fas fa-info-circle"></i> Acessos de demonstração:</strong>
            <p><b>Veterinária:</b> <code>milena@clinicavet.com.br</code> / <code>Milena2024</code></p>
            <p style="margin-top:6px;"><b>Paciente:</b> <code>joao@email.com</code> / <code>Paciente2024</code></p>
        </div>

        <span class="login-back">
            <a href="/index.php"><i class="fas fa-arrow-left"></i> Voltar ao site</a>
        </span>
    </div>
</div>
</body>
</html>
