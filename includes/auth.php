<?php
if (session_status() === PHP_SESSION_NONE) session_start();

const DEMO_USERS = [
    'milena@clinicavet.com.br' => [
        'hash'  => '$2y$10$78XutKhkYcapyB5cxbZ14OZ241MGekXTW/4t/XbmhN7OKIS.49rr.',
        'role'  => 'admin',
        'name'  => 'Dra. Milena Paiva',
        'crmv'  => 'CRMV 12345/SP',
        'avatar'=> 'MP',
    ],
    'joao@email.com' => [
        'hash'  => '$2y$10$.kjbF2DHy20jse4W9F4k6e9UKqMQTMuZMGE5T4ZjTTRW59qrypdjK',
        'role'  => 'paciente',
        'name'  => 'João Silva',
        'crmv'  => '',
        'avatar'=> 'JS',
    ],
];

function auth_login(string $email, string $password): bool {
    $email = strtolower(trim($email));
    if (!isset(DEMO_USERS[$email])) return false;
    $user = DEMO_USERS[$email];
    if (!password_verify($password, $user['hash'])) return false;
    $_SESSION['user'] = [
        'email'  => $email,
        'name'   => $user['name'],
        'role'   => $user['role'],
        'crmv'   => $user['crmv'],
        'avatar' => $user['avatar'],
    ];
    return true;
}

function auth_check(): void {
    if (empty($_SESSION['user'])) {
        header('Location: /Site-pet/pages/login.php');
        exit;
    }
}

function auth_admin(): void {
    auth_check();
    if ($_SESSION['user']['role'] !== 'admin') {
        header('Location: /Site-pet/pages/painel/index.php');
        exit;
    }
}

function auth_user(): array {
    return $_SESSION['user'] ?? [];
}

function auth_logout(): void {
    session_destroy();
    header('Location: /Site-pet/pages/login.php');
    exit;
}

function is_logged(): bool {
    return !empty($_SESSION['user']);
}

function is_admin(): bool {
    return ($_SESSION['user']['role'] ?? '') === 'admin';
}
