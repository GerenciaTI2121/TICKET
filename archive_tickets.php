<?php
session_start();

// 1. Verificação de Segurança (apenas admin pode arquivar)
if (!isset($_SESSION['usuario']) || $_SESSION['usuario'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Acesso negado. Apenas administradores podem arquivar tickets.']);
    exit;
}

// 2. Definir Caminhos dos Arquivos
$currentTicketsFile = 'tickets.json';
$archiveTicketsFile = 'tickets_archive.json'; // Novo arquivo para tickets arquivados

$currentTickets = [];
$archivedTickets = [];
$ticketsToArchive = [];
$remainingTickets = []; // Tickets que permanecerão no arquivo principal

// 3. Carregar Tickets Atuais
if (file_exists($currentTicketsFile)) {
    $jsonContent = file_get_contents($currentTicketsFile);
    $currentTickets = json_decode($jsonContent, true);
    if (!is_array($currentTickets)) {
        $currentTickets = [];
    }
}

// 4. Carregar Tickets Arquivados Existentes (se houver)
if (file_exists($archiveTicketsFile)) {
    $jsonContent = file_get_contents($archiveTicketsFile);
    $archivedTickets = json_decode($jsonContent, true);
    if (!is_array($archivedTickets)) {
        $archivedTickets = [];
    }
}

// 5. Lógica de Arquivamento: Mover tickets do mês passado
$currentMonth = (int)date('m');
$currentYear = (int)date('Y');

foreach ($currentTickets as $ticket) {
    // Certifique-se de que 'createdAt' existe e é uma string de data válida
    if (isset($ticket['createdAt']) && is_string($ticket['createdAt'])) {
        try {
            $ticketDate = new DateTime($ticket['createdAt']);
            $ticketMonth = (int)$ticketDate->format('m');
            $ticketYear = (int)$ticketDate->format('Y');

            // Se o ticket for de um mês/ano anterior ao mês/ano atual, ele é arquivado
            if ($ticketYear < $currentYear || ($ticketYear === $currentYear && $ticketMonth < $currentMonth)) {
                $ticketsToArchive[] = $ticket;
            } else {
                $remainingTickets[] = $ticket; // Mantém no arquivo principal
            }
        } catch (Exception $e) {
            // Lidar com erro de data, manter o ticket no arquivo principal para evitar perda
            $remainingTickets[] = $ticket;
            // Opcional: logar o erro da data inválida
        }
    } else {
        // Se 'createdAt' não existe ou não é string, mantém o ticket no arquivo principal
        $remainingTickets[] = $ticket;
    }
}

if (empty($ticketsToArchive)) {
    echo json_encode(['success' => true, 'message' => 'Nenhum ticket antigo para arquivar.']);
    exit;
}

// 6. Combinar com os tickets já arquivados
$archivedTickets = array_merge($archivedTickets, $ticketsToArchive);

// 7. Salvar os Arquivos Atualizados
if (file_put_contents($currentTicketsFile, json_encode($remainingTickets, JSON_PRETTY_PRINT)) === false) {
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar tickets restantes.']);
    exit;
}
if (file_put_contents($archiveTicketsFile, json_encode($archivedTickets, JSON_PRETTY_PRINT)) === false) {
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar tickets arquivados.']);
    exit;
}

// 8. Definir Permissões para o Novo Arquivo de Arquivo
// (Isso é crucial, pois o arquivo pode ter sido criado com permissões erradas pelo PHP)
if (file_exists($archiveTicketsFile)) {
    chown($archiveTicketsFile, 'www-data');
    chgrp($archiveTicketsFile, 'www-data');
    chmod($archiveTicketsFile, 0664); // Permissões de leitura/escrita para www-data
}


echo json_encode([
    'success' => true,
    'message' => count($ticketsToArchive) . ' tickets arquivados com sucesso!',
    'archived_count' => count($ticketsToArchive)
]);

?>