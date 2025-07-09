<?php
// get_report_data.php
require_once 'db_config.php';

header('Content-Type: application/json');

try {
    // Busca todos os tickets, independentemente do status, para o relatório
    $stmt = $pdo->prepare("SELECT * FROM tickets ORDER BY createdAt DESC");
    $stmt->execute();
    $tickets = $stmt->fetchAll();

    // Decodifica a string JSON da coluna 'techs' de volta para array PHP
    foreach ($tickets as &$ticket) {
        if (isset($ticket['techs'])) {
            $ticket['techs'] = json_decode($ticket['techs'], true);
            // Garante que techs seja um array mesmo se json_decode falhar ou for nulo
            if (!is_array($ticket['techs'])) {
                $ticket['techs'] = [];
            }
        } else {
            $ticket['techs'] = []; // Garante que techs existe e é um array vazio se não houver no banco
        }
    }
    unset($ticket); // Desreferencia a última variável

    echo json_encode(['success' => true, 'tickets' => $tickets]);

} catch (PDOException $e) {
    error_log("Erro ao obter dados para relatório: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao obter dados para o relatório.']);
}
?>