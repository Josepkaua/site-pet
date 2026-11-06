<?php
// ============================================================
// Configuração central
//
// SEGREDOS NÃO FICAM NO CÓDIGO. Defina-os como variáveis de
// ambiente (Render → Environment) ou em includes/config.local.php
// (arquivo ignorado pelo git). Veja o README.
// ============================================================

if (is_file(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';   // pode definir as mesmas constantes
}

function env(string $key, string $default = ''): string {
    $v = getenv($key);
    if ($v === false || $v === '') $v = $_ENV[$key] ?? $_SERVER[$key] ?? '';
    return $v !== '' ? (string)$v : $default;
}

// ── Nhost / Hasura ──────────────────────────────────────────
defined('NHOST_SUBDOMAIN')    || define('NHOST_SUBDOMAIN',    env('NHOST_SUBDOMAIN', 'kdpedkepakvhoppsdexh'));
defined('NHOST_REGION')       || define('NHOST_REGION',       env('NHOST_REGION', 'sa-east-1'));
defined('NHOST_ADMIN_SECRET') || define('NHOST_ADMIN_SECRET', env('NHOST_ADMIN_SECRET'));

define('HASURA_BASE', 'https://' . NHOST_SUBDOMAIN . '.hasura.' . NHOST_REGION . '.nhost.run');
define('GRAPHQL_URL', HASURA_BASE . '/v1/graphql');
define('DB_CONFIGURED', NHOST_ADMIN_SECRET !== '');

// ── Dados da clínica (padrões; sobrescritos por data/info.json) ─
$__info = [
    'nome'      => 'Clínica Veterinária Dra. Milena Paiva',
    'dra'       => 'Dra. Milena Paiva',
    'telefone'  => '(11) 99999-9999',
    'whatsapp'  => '5511999999999',
    'email'     => 'contato@milenapaivevet.com.br',
    'endereco'  => 'Rua dos Animais, 123 – São Paulo, SP',
    'crmv'      => 'CRMV 12345/SP',
    'instagram' => 'https://instagram.com',
    'facebook'  => 'https://facebook.com',
    'sobre'     => 'Com mais de 10 anos de experiência no atendimento de pequenos animais, Dra. Milena Paiva é apaixonada pelo que faz.',
];
$__infoFile = __DIR__ . '/../data/info.json';
if (is_file($__infoFile)) {
    $saved = json_decode((string)@file_get_contents($__infoFile), true);
    if (is_array($saved)) {
        foreach ($saved as $k => $v) {
            // Dados antigos foram salvos com htmlspecialchars – normaliza para texto puro
            $v = trim(strip_tags(html_entity_decode((string)$v, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
            if ($v !== '' && array_key_exists($k, $__info)) $__info[$k] = $v;
        }
    }
}
$__info['whatsapp'] = preg_replace('/\D/', '', $__info['whatsapp']);

define('CLINIC_NAME',      $__info['nome']);
define('CLINIC_DRA',       $__info['dra']);
define('CLINIC_PHONE',     $__info['telefone']);
define('CLINIC_WHATS',     $__info['whatsapp']);
define('CLINIC_EMAIL',     $__info['email']);
define('CLINIC_ADDRESS',   $__info['endereco']);
define('CLINIC_CRMV',      $__info['crmv']);
define('CLINIC_INSTAGRAM', $__info['instagram']);
define('CLINIC_FACEBOOK',  $__info['facebook']);
define('CLINIC_ABOUT',     $__info['sobre']);
unset($__info, $__infoFile, $saved, $k, $v);

// ============================================================
// GraphQL helper (sempre use $variables – nunca concatene input)
// ============================================================
function gql(string $query, array $variables = []): array {
    if (!DB_CONFIGURED) {
        return ['ok' => false, 'data' => [], 'errors' => [['message' => 'NHOST_ADMIN_SECRET não configurado']]];
    }
    $ch = curl_init(GRAPHQL_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT        => 12,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'x-hasura-admin-secret: ' . NHOST_ADMIN_SECRET,
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'query'     => $query,
            'variables' => empty($variables) ? new stdClass() : $variables,
        ]),
    ]);

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr   = curl_error($ch);
    curl_close($ch);

    $decoded = is_string($response) ? (json_decode($response, true) ?? []) : [];
    $ok      = $http_code === 200 && empty($decoded['errors']);

    if (!$ok) {
        error_log('[gql] HTTP ' . $http_code . ' ' . $curlErr . ' ' . json_encode($decoded['errors'] ?? []));
    }

    return [
        'ok'     => $ok,
        'data'   => is_array($decoded['data'] ?? null) ? $decoded['data'] : [],
        'errors' => $decoded['errors'] ?? [],
    ];
}
