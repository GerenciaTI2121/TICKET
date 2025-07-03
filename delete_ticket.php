<?php
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos: ID do ticket ausente.']);
    exit;
}

$id = $data['id'];

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
// Iterar com o índice para remover o item corretamente
foreach ($tickets as $key => $ticket) {
    if (isset($ticket['id']) && $ticket['id'] === $id) {
        unset($tickets[$key]); // Remove o elemento do array
        $found = true;
        break;
    }
}

if (!$found) {
    echo json_encode(['success' => false, 'message' => 'Ticket não encontrado para exclusão.']);
    exit;
}

// Reindexar o array após a remoção para evitar lacunas (chaves numéricas consecutivas)
$tickets = array_values($tickets);

if (file_put_contents($file, json_encode($tickets, JSON_PRETTY_PRINT)) === false) {
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar após a exclusão do ticket.']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Ticket excluído com sucesso.']);
?>