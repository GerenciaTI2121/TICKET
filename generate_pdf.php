<?php
// generate_pdf.php com DOMPDF
require_once 'db_config.php';

// Verifique e ajuste o caminho para o autoload do DOMPDF
// Se você instalou via Composer, o caminho geralmente é 'vendor/autoload.php'
require_once 'vendor/autoload.php'; // <--- AJUSTE ESTE CAMINHO SE NECESSÁRIO

use Dompdf\Dompdf;
use Dompdf\Options;

// 1. Obter os dados dos tickets do MySQL
try {
    $stmt = $pdo->prepare("SELECT * FROM tickets ORDER BY createdAt DESC");
    $stmt->execute();
    $tickets = $stmt->fetchAll();

    // Decodifica a string JSON da coluna 'techs' de volta para array PHP
    foreach ($tickets as &$ticket) {
        if (isset($ticket['techs'])) {
            $ticket['techs'] = json_decode($ticket['techs'], true);
        }
    }
    unset($ticket); // Desreferencia a última variável

} catch (PDOException $e) {
    error_log("Erro ao obter tickets para PDF: " . $e->getMessage());
    die('Erro ao carregar dados para o relatório PDF.');
}

// 2. Construir o conteúdo HTML para o PDF
$html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Relatório de Tickets</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { text-align: center; color: #333; }
        .ticket-card {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .ticket-card h2 {
            margin-top: 0;
            color: #0056b3;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .ticket-card p {
            margin: 5px 0;
            line-height: 1.5;
        }
        .ticket-card p strong {
            color: #555;
        }
        .status-aberto { color: #d9534f; } /* Vermelho */
        .status-resolvido { color: #5cb85c; } /* Verde */
        .status-arquivado { color: #5bc0de; } /* Azul claro */
        .priority-alta { color: #d9534f; font-weight: bold; }
        .priority-media { color: #f0ad4e; }
        .priority-baixa { color: #5cb85c; }
    </style>
</head>
<body>
    <h1>Relatório de Tickets</h1>';

if (empty($tickets)) {
    $html .= '<p style="text-align: center;">Nenhum ticket encontrado para gerar o relatório.</p>';
} else {
    foreach ($tickets as $ticket) {
        $html .= '<div class="ticket-card">';
        $html .= '<h2>Ticket ID: ' . htmlspecialchars($ticket['id']) . '</h2>';
        $html .= '<p><strong>Usuário:</strong> ' . htmlspecialchars($ticket['usuario']) . '</p>';
        $html .= '<p><strong>Setor:</strong> ' . htmlspecialchars($ticket['sector']) . '</p>';
        $html .= '<p><strong>Tipo:</strong> ' . htmlspecialchars($ticket['type']) . '</p>';
        
        // Adiciona classe CSS para prioridade
        $priorityClass = '';
        switch (strtolower($ticket['priority'])) {
            case 'alta': $priorityClass = 'priority-alta'; break;
            case 'media': $priorityClass = 'priority-media'; break;
            case 'baixa': $priorityClass = 'priority-baixa'; break;
        }
        $html .= '<p><strong>Prioridade:</strong> <span class="' . $priorityClass . '">' . htmlspecialchars($ticket['priority']) . '</span></p>';
        
        // Adiciona classe CSS para status
        $statusClass = '';
        switch (strtolower($ticket['status'])) {
            case 'aberto': $statusClass = 'status-aberto'; break;
            case 'resolvido': $statusClass = 'status-resolvido'; break;
            case 'arquivado': $statusClass = 'status-arquivado'; break;
        }
        $html .= '<p><strong>Status:</strong> <span class="' . $statusClass . '">' . htmlspecialchars($ticket['status']) . '</span></p>';
        
        $html .= '<p><strong>Descrição:</strong> ' . nl2br(htmlspecialchars($ticket['description'])) . '</p>';
        
        $techsList = 'N/A';
        if (!empty($ticket['techs'])) {
            $techsList = implode(', ', array_map('htmlspecialchars', $ticket['techs']));
        }
        $html .= '<p><strong>Técnicos:</strong> ' . $techsList . '</p>';
        
        $html .= '<p><strong>Criado em:</strong> ' . htmlspecialchars($ticket['createdAt']) . '</p>';
        $html .= '<p><strong>Última Atualização:</strong> ' . htmlspecialchars($ticket['updatedAt']) . '</p>';
        $html .= '</div>';
    }
}

$html .= '</body>
</html>';

// 3. Configurar e gerar o PDF com DOMPDF
$options = new Options();
$options->set('defaultFont', 'Arial'); // Define uma fonte padrão para evitar problemas
$options->set('isHtml5ParserEnabled', true); // Habilita o parser HTML5
$options->set('isRemoteEnabled', false); // Desabilita o carregamento de recursos externos (imagens, CSS) via URL, por segurança e performance

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);

// (Opcional) Configurar o tamanho e a orientação do papel
$dompdf->setPaper('A4', 'portrait');

// Renderizar o HTML para PDF
$dompdf->render();

// Enviar o PDF para o navegador
$dompdf->stream("relatorio_tickets.pdf", ["Attachment" => false]); // "Attachment" => false para exibir no navegador
?>