<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit;
}

$required = ['nome', 'telefone', 'pet_nome', 'data'];
foreach ($required as $f) {
    if (empty($input[$f])) {
        echo json_encode(['success' => false, 'message' => 'Campo obrigatório: ' . $f]);
        exit;
    }
}

$mutation = '
mutation InsertAgendamento($obj: agendamentos_insert_input!) {
    insert_agendamentos_one(object: $obj) { id }
}';

$vars = ['obj' => [
    'nome'        => htmlspecialchars(trim($input['nome']),        ENT_QUOTES),
    'telefone'    => htmlspecialchars(trim($input['telefone']),    ENT_QUOTES),
    'email'       => htmlspecialchars(trim($input['email'] ?? ''), ENT_QUOTES),
    'pet_nome'    => htmlspecialchars(trim($input['pet_nome']),    ENT_QUOTES),
    'pet_especie' => htmlspecialchars(trim($input['pet_especie'] ?? ''), ENT_QUOTES),
    'pet_raca'    => htmlspecialchars(trim($input['pet_raca']    ?? ''), ENT_QUOTES),
    'servico'     => htmlspecialchars(trim($input['servico']     ?? ''), ENT_QUOTES),
    'data'        => $input['data'],
    'horario'     => htmlspecialchars(trim($input['horario'] ?? ''), ENT_QUOTES),
    'obs'         => htmlspecialchars(trim($input['obs']     ?? ''), ENT_QUOTES),
    'status'      => 'pendente',
]];

$result = gql($mutation, $vars);

if ($result['ok']) {
    echo json_encode(['success' => true, 'message' => 'Agendamento realizado! Confirmaremos via WhatsApp em breve.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar. Ligue para ' . CLINIC_PHONE]);
}
