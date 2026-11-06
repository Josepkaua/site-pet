<?php
// API JSON para agendamento de consulta (usada por formulários via fetch).
// Exige o cabeçalho X-CSRF-Token (lido da <meta name="csrf-token"> da página).
require_once __DIR__ . '/../includes/bootstrap.php';
header('Content-Type: application/json; charset=UTF-8');

function respond(bool $ok, string $msg, int $code = 200): void {
    http_response_code($code);
    echo json_encode(['success' => $ok, 'message' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(false, 'Método não permitido.', 405);

$input = json_decode((string)file_get_contents('php://input', false, null, 0, 20000), true);
if (!is_array($input)) respond(false, 'Dados inválidos.', 400);
if (!empty($input['website'])) respond(true, 'Agendamento realizado! Confirmaremos via WhatsApp em breve.');

$obj = [
    'nome'        => clean_text($input['nome'] ?? '', 120),
    'telefone'    => clean_phone($input['telefone'] ?? ''),
    'email'       => clean_email($input['email'] ?? ''),
    'pet_nome'    => clean_text($input['pet_nome'] ?? '', 80),
    'pet_especie' => clean_text($input['pet_especie'] ?? '', 30),
    'pet_raca'    => clean_text($input['pet_raca'] ?? '', 80),
    'servico'     => clean_text($input['servico'] ?? '', 80),
    'data'        => clean_future_date($input['data'] ?? ''),
    'horario'     => clean_text($input['horario'] ?? '', 20),
    'obs'         => clean_text($input['obs'] ?? '', 1000, true),
    'status'      => 'pendente',
];

foreach (['nome' => 'nome', 'telefone' => 'telefone', 'pet_nome' => 'nome do pet', 'data' => 'data'] as $f => $label) {
    if ($obj[$f] === '') respond(false, 'Campo obrigatório ou inválido: ' . $label, 422);
}
if (!rate_limit('agendamento', 5, 3600)) respond(false, 'Muitas solicitações. Tente mais tarde.', 429);

$result = gql('mutation($obj: agendamentos_insert_input!) { insert_agendamentos_one(object: $obj) { id } }', ['obj' => $obj]);

$result['ok']
    ? respond(true, 'Agendamento realizado! Confirmaremos via WhatsApp em breve.')
    : respond(false, 'Erro ao salvar. Ligue para ' . CLINIC_PHONE, 502);
