<?php
require_once 'db_config.php';

header('Content-Type: application/json');

try {
    // Contagem de tickets por status
    $stmtStatus = $pdo->query("SELECT status, COUNT(*) as count FROM tickets GROUP BY status");
    $statusCounts = $stmtStatus->fetchAll(PDO::FETCH_KEY_PAIR); // Retorna um array associativo [status => count]

    // Contagem de tickets por setor
    $stmtSector = $pdo->query("SELECT sector, COUNT(*) as count FROM tickets GROUP BY sector");
    $sectorCounts = $stmtSector->fetchAll(PDO::FETCH_ASSOC);

    // Contagem de tickets por tipo
    $stmtType = $pdo->query("SELECT type, COUNT(*) as count FROM tickets GROUP BY type");
    $typeCounts = $stmtType->fetchAll(PDO::FETCH_ASSOC);

    // Contagem de tickets por prioridade
    $stmtPriority = $pdo->query("SELECT priority, COUNT(*) as count FROM tickets GROUP BY priority");
    $priorityCounts = $stmtPriority->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => $statusCounts,
        'sector' => $sectorCounts,
        'type' => $typeCounts,
        'priority' => $priorityCounts
    ]);

} catch (PDOException $e) {
    error_log("Erro ao gerar relatório: " . $e->getMessage());
    echo json_encode(['error' => 'Erro ao gerar relatório.']);
}
?>