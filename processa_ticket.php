<?php
require_once 'db_config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $id = uniqid('ticket_', true); // Gera um ID único
    $usuario = $data['usuario'] ?? '';
    $sector = $data['sector'] ?? '';
    $type = $data['type'] ?? '';
    $priority = $data['priority'] ?? '';
    $description = $data['description'] ?? '';
    $status = 'Aberto'; // Novo ticket sempre começa como Aberto
    $techs = json_encode($data['techs'] ?? []); // Armazena como JSON string
    $createdAt = date('Y-m-d H:i:s');

    try {
        $stmt = $pdo->prepare("INSERT INTO tickets (id, usuario, sector, type, priority, description, status, techs, createdAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id, $usuario, $sector, $type, $priority, $description, $status, $techs, $createdAt]);

        echo json_encode(['success' => true, 'message' => 'Ticket criado com sucesso!', 'ticketId' => $id]);
    } catch (PDOException $e) {
        error_log("Erro ao criar ticket: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Erro ao criar ticket.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método de requisição inválido.']);
}
?>