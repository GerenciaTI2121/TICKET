<?php
require_once 'db_config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $id = $data['id'] ?? '';
    $status = $data['status'] ?? '';
    $techs = json_encode($data['techs'] ?? []); // Pode ser atualizado também

    // Validação simples para status
    $allowedStatuses = ['Aberto', 'Resolvido', 'Arquivado'];
    if (!in_array($status, $allowedStatuses)) {
        echo json_encode(['success' => false, 'message' => 'Status inválido.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE tickets SET status = ?, techs = ? WHERE id = ?");
        $stmt->execute([$status, $techs, $id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Ticket atualizado com sucesso!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Ticket não encontrado ou nenhum dado alterado.']);
        }
    } catch (PDOException $e) {
        error_log("Erro ao atualizar ticket: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar ticket.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método de requisição inválido.']);
}
?>