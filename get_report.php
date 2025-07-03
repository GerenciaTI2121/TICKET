<?php
session_start();
header('Content-Type: application/json');

// Opcional: Proteger para que apenas o admin possa gerar relatórios
if (!isset($_SESSION['usuario']) || $_SESSION['usuario'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

$file = 'tickets.json';
if (!file_exists($file)) {
    echo json_encode([]);
    exit;
}

$tickets = json_decode(file_get_contents($file), true);

if (!is_array($tickets)) {
    echo json_encode([]);
    exit;
}

// Opcional: Adicionar lógica para filtrar os tickets se houver parâmetros na URL
// Ex: $status_filtro = $_GET['status'] ?? null;
// Ex: $tickets_filtrados = array_filter($tickets, function($ticket) use ($status_filtro) {
//     return $status_filtro ? ($ticket['status'] === $status_filtro) : true;
// });

echo json_encode(array_values($tickets)); // Retorna todos os tickets
?>