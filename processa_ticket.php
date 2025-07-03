<?php
session_start();
header('Content-Type: application/json'); // Define o tipo de conteúdo como JSON

function limpar($str) {
    // Usa ENT_QUOTES para codificar aspas simples e duplas
    return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Em vez de redirecionar, retorna um erro JSON para requisições não-POST
    echo json_encode(['success' => false, 'message' => 'Método de requisição inválido.']);
    exit;
}

$usuario = $_SESSION['usuario'] ?? 'desconhecido';

// Recebendo e limpando dados
$techs = json_decode($_POST['techs'] ?? '[]', true);
$techs = is_array($techs) ? array_map('limpar', $techs) : [];
$sector = limpar($_POST['sector'] ?? '');
$type = limpar($_POST['type'] ?? '');
$priority = limpar(urldecode($_POST['priority'] ?? ''));
$description = limpar($_POST['description'] ?? '');

// Validações
$errors = [];
if (count($techs) === 0) $errors[] = 'Selecione pelo menos um funcionário da TI.';
if ($sector === '') $errors[] = 'Informe o setor.';
if ($type === '') $errors[] = 'Informe o tipo de serviço.';
if ($priority === '') $errors[] = 'Informe a prioridade.';
if ($description === '') $errors[] = 'Informe a descrição.';

if (count($errors) > 0) {
    // Retorna os erros em formato JSON
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// Criar ticket com id único
$ticket = [
    'id' => uniqid('', true), // Gera um ID único baseado no timestamp microtime
    'usuario' => $usuario,
    'techs' => $techs,
    'sector' => $sector,
    'type' => $type,
    'priority' => $priority,
    'description' => $description, // A descrição já foi limpa com htmlspecialchars
    'status' => 'Aberto',
    'createdAt' => date('c'), // Formato ISO 8601
];

// Salvar no arquivo JSON
$file = 'tickets.json';
$tickets = [];

if (file_exists($file)) {
    $json = file_get_contents($file);
    $tickets = json_decode($json, true);
    if (!is_array($tickets)) {
        $tickets = []; // Garante que é um array, mesmo se o JSON estiver corrompido
    }
}

$tickets[] = $ticket;

// Tenta salvar o arquivo
if (file_put_contents($file, json_encode($tickets, JSON_PRETTY_PRINT)) === false) {
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar o ticket. Tente novamente.']);
    exit;
}

// Retorna sucesso em formato JSON
echo json_encode(['success' => true, 'message' => 'Ticket criado com sucesso!']);
?>