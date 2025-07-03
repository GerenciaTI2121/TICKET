<?php
session_start();
header('Content-Type: application/json');

function limpar($str) {
    // Usa ENT_QUOTES para codificar aspas simples e duplas
    // O htmlspecialchars deve ser aplicado *após* a decodificação da URL,
    // e serve para segurança ao exibir em HTML.
    return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método de requisição inválido.']);
    exit;
}

$usuario = $_SESSION['usuario'] ?? 'desconhecido';

// Recebendo e limpando dados
// Decodificar antes de limpar para garantir que os caracteres especiais sejam tratados
$techs_raw = $_POST['techs'] ?? '[]';
$techs_decoded = json_decode(urldecode($techs_raw), true); // Decodifica a string JSON *e* os caracteres da URL dentro dela

// Garante que é um array antes de mapear e limpar
$techs = is_array($techs_decoded) ? array_map('limpar', $techs_decoded) : [];

// Aplicar urldecode() diretamente nos valores do $_POST antes de passar para limpar()
$sector = limpar(urldecode($_POST['sector'] ?? ''));
$priority = limpar(urldecode($_POST['priority'] ?? ''));
$type = limpar(urldecode($_POST['type'] ?? ''));
$description = limpar(urldecode($_POST['description'] ?? ''));


// Validações
$errors = [];
if (count($techs) === 0) $errors[] = 'Selecione pelo menos um funcionário da TI.';
if ($sector === '') $errors[] = 'Informe o setor.';
if ($type === '') $errors[] = 'Informe o tipo de serviço.';
if ($priority === '') $errors[] = 'Informe a prioridade.';
if ($description === '') $errors[] = 'Informe a descrição.';

if (count($errors) > 0) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// Criar ticket com id único
$ticket = [
    'id' => uniqid('', true),
    'usuario' => $usuario,
    'techs' => $techs,
    'sector' => $sector,
    'type' => $type,
    'priority' => $priority,
    'description' => $description,
    'status' => 'Aberto',
    'createdAt' => date('c'),
];

// Salvar no arquivo JSON
$file = 'tickets.json';
$tickets = [];

if (file_exists($file)) {
    $json = file_get_contents($file);
    $tickets = json_decode($json, true);
    if (!is_array($tickets)) {
        $tickets = [];
    }
}

$tickets[] = $ticket;

if (file_put_contents($file, json_encode($tickets, JSON_PRETTY_PRINT)) === false) {
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar o ticket. Tente novamente.']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Ticket criado com sucesso!']);
?>