<?php
require_once 'db_config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("UPDATE tickets SET status = 'Arquivado' WHERE status = 'Resolvido'");
        $stmt->execute();

        $rowsAffected = $stmt->rowCount();
        echo json_encode(['success' => true, 'message' => "$rowsAffected tickets resolvidos foram arquivados com sucesso."]);
    } catch (PDOException $e) {
        error_log("Erro ao arquivar tickets: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Erro ao arquivar tickets.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método de requisição inválido.']);
}
?>