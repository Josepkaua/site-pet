<?php
// ============================================================
// Bootstrap: incluído no topo de TODA página.
//  - cabeçalhos de segurança
//  - sessão com cookies seguros
//  - proteção CSRF automática em todo POST
//  - config + helpers
// ============================================================

if (defined('APP_BOOTSTRAPPED')) return;
define('APP_BOOTSTRAPPED', true);

date_default_timezone_set('America/Sao_Paulo');
mb_internal_encoding('UTF-8');

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

// ── Cabeçalhos de segurança ─────────────────────────────────
if (PHP_SAPI !== 'cli' && !headers_sent()) {
    header_remove('X-Powered-By');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
    header("Content-Security-Policy: default-src 'self'; "
         . "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; "
         . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; "
         . "font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com; "
         . "img-src 'self' data:; connect-src 'self'; "
         . "frame-ancestors 'self'; base-uri 'self'; form-action 'self'; object-src 'none'");
    if ($isHttps) header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

// ── Sessão ──────────────────────────────────────────────────
if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('vetsess');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

// ── CSRF: injeta token em todo <form method="post"> e valida POSTs ─
if (PHP_SAPI !== 'cli') {
    ob_start(static function (string $html): string {
        if (stripos($html, '<form') === false) return $html;
        return preg_replace(
            '/(<form\b[^>]*\bmethod\s*=\s*["\']?post["\']?[^>]*>)/i',
            '$1' . csrf_field(),
            $html
        ) ?? $html;
    });

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && !defined('CSRF_SKIP_AUTO')) {
        $sent = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!csrf_valid((string)$sent)) {
            http_response_code(419);
            ob_end_clean();
            header('Content-Type: text/html; charset=UTF-8');
            echo '<!DOCTYPE html><meta charset="utf-8"><title>Sessão expirada</title>'
               . '<div style="font-family:sans-serif;max-width:480px;margin:80px auto;text-align:center;">'
               . '<h1 style="color:#2e9e6b">Sessão expirada</h1>'
               . '<p>Por segurança, seu formulário expirou. Volte, recarregue a página e tente novamente.</p>'
               . '<p><a href="javascript:history.back()">← Voltar</a></p></div>';
            exit;
        }
    }
}
