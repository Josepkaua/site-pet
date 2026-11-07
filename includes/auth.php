<?php
require_once __DIR__ . '/bootstrap.php';

const SESSION_IDLE_LIMIT = 7200; // 2h sem atividade → sai

/**
 * Usuários. O admin pode (e deve) ser configurado por variáveis de ambiente:
 *   ADMIN_EMAIL, ADMIN_PASSWORD_HASH  (gere com: php -r "echo password_hash('SuaSenha', PASSWORD_DEFAULT);")
 * Sem elas, usa a senha padrão de demonstração e o painel mostra um aviso.
 */
function auth_users(): array {
    static $users = null;
    if ($users !== null) return $users;

    $adminEmail = strtolower(env('ADMIN_EMAIL', 'milena@clinicavet.com.br'));
    $adminHash  = env('ADMIN_PASSWORD_HASH', '$2y$10$78XutKhkYcapyB5cxbZ14OZ241MGekXTW/4t/XbmhN7OKIS.49rr.');

    $users = [
        $adminEmail => [
            'hash' => $adminHash, 'role' => 'admin', 'name' => CLINIC_DRA,
            'crmv' => CLINIC_CRMV, 'avatar' => 'MP',
        ],
    ];
    if (env('DEMO_PATIENT', '1') === '1') {
        $users['joao@email.com'] = [
            'hash' => '$2y$10$.kjbF2DHy20jse4W9F4k6e9UKqMQTMuZMGE5T4ZjTTRW59qrypdjK',
            'role' => 'paciente', 'name' => 'João Silva', 'crmv' => '', 'avatar' => 'JS',
        ];
    }
    return $users;
}

function admin_using_default_password(): bool {
    return env('ADMIN_PASSWORD_HASH') === '';
}

function auth_login(string $email, string $password): bool {
    $email = strtolower(trim($email));
    $users = auth_users();
    // Executa password_verify mesmo para e-mail inexistente (tempo constante)
    $hash  = $users[$email]['hash'] ?? '$2y$10$usesomesillystringforsaltusesomesillystringfore.';
    if (!password_verify($password, $hash) || !isset($users[$email])) return false;

    $user = $users[$email];
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'email'  => $email,
        'name'   => $user['name'],
        'role'   => $user['role'],
        'crmv'   => $user['crmv'],
        'avatar' => $user['avatar'],
    ];
    $_SESSION['last_seen'] = time();
    unset($_SESSION['_csrf']);           // novo token após login
    return true;
}

function auth_touch(): void {
    if (empty($_SESSION['user'])) return;
    if (time() - ($_SESSION['last_seen'] ?? 0) > SESSION_IDLE_LIMIT) {
        $_SESSION = [];
        session_regenerate_id(true);
        return;
    }
    $_SESSION['last_seen'] = time();
}
auth_touch();

function auth_check(): void {
    if (empty($_SESSION['user'])) {
        header('Location: /pages/login.php');
        exit;
    }
}

function auth_admin(): void {
    auth_check();
    if (($_SESSION['user']['role'] ?? '') !== 'admin') {
        header('Location: /pages/painel/index.php');
        exit;
    }
}

function auth_user(): array {
    return $_SESSION['user'] ?? [];
}

function auth_logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000, 'path' => $p['path'], 'domain' => $p['domain'],
            'secure' => $p['secure'], 'httponly' => $p['httponly'], 'samesite' => $p['samesite'] ?? 'Lax',
        ]);
    }
    session_destroy();
    header('Location: /pages/login.php');
    exit;
}

function is_logged(): bool {
    return !empty($_SESSION['user']);
}

function is_admin(): bool {
    return ($_SESSION['user']['role'] ?? '') === 'admin';
}
