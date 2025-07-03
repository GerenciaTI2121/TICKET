<?php
session_start();
header('Content-Type: application/json');

$usuario = $_SESSION['usuario'] ?? '';

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

// Se for admin, retorna todos
if ($usuario === 'admin') {
    echo json_encode($tickets);
    exit;
}

// Senão, só os tickets do usuário logado
$tickets_filtrados = array_filter($tickets, function($ticket) use ($usuario) {
    return isset($ticket['usuario']) && $ticket['usuario'] === $usuario;
});

echo json_encode(array_values($tickets_filtrados));
