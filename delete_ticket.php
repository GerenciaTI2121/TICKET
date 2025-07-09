<?php
require_once 'db_config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? '';

    try {
        $stmt = $pdo->prepare("DELETE FROM tickets WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Ticket excluído com sucesso!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Ticket não encontrado.']);
        }
    } catch (PDOException $e) {
        error_log("Erro ao excluir ticket: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir ticket.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método de requisição inválido.']);
}
?>