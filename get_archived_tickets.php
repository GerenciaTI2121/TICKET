<?php
require_once 'db_config.php';

header('Content-Type: application/json');

try {
    // Obter os parâmetros de ano e mês da URL
    $year = $_GET['year'] ?? null;
    $month = $_GET['month'] ?? null;

    $sql = "SELECT * FROM tickets WHERE status = 'Arquivado'";
    $params = [];

    // Adicionar filtro por ano se for fornecido
    if ($year && $year !== 'all') { // 'all' pode ser um valor para não filtrar por ano
        $sql .= " AND YEAR(createdAt) = :year";
        $params[':year'] = $year;
    }

    // Adicionar filtro por mês se for fornecido (e o mês não for 'all')
    if ($month && $month !== 'all') { // 'all' pode ser um valor para não filtrar por mês
        $sql .= " AND MONTH(createdAt) = :month";
        $params[':month'] = $month;
    }

    $sql .= " ORDER BY createdAt DESC"; // Sempre ordenar por data de criação

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params); // Passa os parâmetros para o execute

    $tickets = $stmt->fetchAll();

    // Decodifica a string JSON da coluna 'techs' de volta para array PHP
    foreach ($tickets as &$ticket) {
        if (isset($ticket['techs'])) {
            $ticket['techs'] = json_decode($ticket['techs'], true);
        }
    }
    unset($ticket);

    echo json_encode($tickets);
} catch (PDOException $e) {
    error_log("Erro ao obter tickets arquivados: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao carregar tickets arquivados.']); // Retorna JSON de erro consistente
}
?>