<?php
require_once 'db_config.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare("SELECT * FROM tickets WHERE status IN ('Aberto', 'Resolvido') ORDER BY createdAt DESC");
    $stmt->execute();
    $tickets = $stmt->fetchAll();

    // Decodifica a string JSON da coluna 'techs' de volta para array PHP
    foreach ($tickets as &$ticket) {
        if (isset($ticket['techs'])) {
            $ticket['techs'] = json_decode($ticket['techs'], true);
        }
    }
    unset($ticket); // Desreferencia a última variável

    echo json_encode($tickets);
} catch (PDOException $e) {
    error_log("Erro ao obter tickets: " . $e->getMessage());
    echo json_encode([]); // Retorna array vazio em caso de erro
}
?>