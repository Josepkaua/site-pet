<?php
// ============================================================
// Nhost / Hasura Configuration
// ============================================================
define('NHOST_SUBDOMAIN', 'kdpedkepakvhoppsdexh');
define('NHOST_REGION',    'sa-east-1');
define('NHOST_ADMIN_SECRET', '=uVw1t5X*@ty^yRAU*uI,P8(14G,!HE5');

define('GRAPHQL_URL', 'https://' . NHOST_SUBDOMAIN . '.hasura.' . NHOST_REGION . '.nhost.run/v1/graphql');

// ============================================================
// Clinic Info
// ============================================================
define('CLINIC_NAME',    'Clínica Veterinária Dra. Milena Paiva');
define('CLINIC_DRA',     'Dra. Milena Paiva');
define('CLINIC_PHONE',   '(11) 99999-9999');
define('CLINIC_WHATS',   '5511999999999');
define('CLINIC_EMAIL',   'contato@milenapaivevet.com.br');
define('CLINIC_ADDRESS', 'Rua dos Animais, 123 – São Paulo, SP');
define('CLINIC_CRMV',    'CRMV 12345/SP');

// ============================================================
// GraphQL helper
// ============================================================
function gql(string $query, array $variables = []): array {
    $ch = curl_init(GRAPHQL_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_SSL_VERIFYPEER => false,
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
    curl_close($ch);

    $decoded = json_decode($response, true) ?? [];

    return [
        'ok'     => $http_code === 200 && empty($decoded['errors']),
        'data'   => $decoded['data']  ?? [],
        'errors' => $decoded['errors'] ?? [],
    ];
}
