<?php
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'], $data['status'])) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos (ID ou Status ausente).']);
    exit;
}

$id = $data['id'];
$status = $data['status'];

// Validação do status para garantir que apenas valores permitidos sejam definidos
$allowedStatuses = ['Aberto', 'Resolvido'];
if (!in_array($status, $allowedStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Status inválido fornecido.']);
    exit;
}

$file = 'tickets.json';

if (!file_exists($file)) {
    echo json_encode(['success' => false, 'message' => 'Arquivo de tickets não encontrado.']);
    exit;
}

$json_content = file_get_contents($file);
$tickets = json_decode($json_content, true);

if (!is_array($tickets)) {
    echo json_encode(['success' => false, 'message' => 'Formato de arquivo de tickets inválido.']);
    exit;
}

$found = false;
foreach ($tickets as &$ticket) { // Usa & para modificar o array original
    if (isset($ticket['id']) && $ticket['id'] === $id) {
        $ticket['status'] = $status;
        $found = true;
        break;
    }
}

if (!$found) {
    echo json_encode(['success' => false, 'message' => 'Ticket não encontrado para atualização.']);
    exit;
}

if (file_put_contents($file, json_encode($tickets, JSON_PRETTY_PRINT)) === false) {
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar as alterações do ticket.']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Ticket atualizado com sucesso.']);
?>