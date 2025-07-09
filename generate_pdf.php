<?php
// ATENÇÃO: As linhas abaixo são para depuração e devem ser REMOVIDAS em ambiente de produção!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// FIM DAS LINHAS DE DEPURAÇÃO

session_start();
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario'])) {
    // Redireciona para a página de login ou exibe uma mensagem de erro
    echo "Acesso negado. Por favor, faça login.";
    exit;
}

// Verifica se o usuário tem permissão para gerar o relatório (apenas 'admin' pode)
if ($_SESSION['usuario'] !== 'admin') {
    echo "Você não tem permissão para gerar este relatório.";
    exit;
}

header('Content-Type: text/html; charset=UTF-8'); // Garante que o navegador interprete UTF-8

$file = 'tickets.json';
$tickets = [];

if (file_exists($file)) {
    $json_content = file_get_contents($file);
    $tickets = json_decode($json_content, true);
    if (!is_array($tickets)) {
        $tickets = []; // Garante que $tickets seja um array em caso de JSON inválido
    }
}

// Inicia a geração do HTML para o PDF
$html = '
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Tickets de Serviço</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1 { text-align: center; color: #2ecc71; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .status-Aberto { color: #f39c12; font-weight: bold; }
        .status-Resolvido { color: #27ae60; font-weight: bold; }
        .footer { text-align: center; margin-top: 30px; font-size: 8px; color: #555; }
    </style>
</head>
<body>
    <h1>Relatório de Tickets de Serviço</h1>
    <table>
        <thead>
            <tr>
                <th>ID do Ticket</th>
                <th>Usuário</th>
                <th>Setor</th>
                <th>Tipo</th>
                <th>Prioridade</th>
                <th>Status</th>
                <th>Atribuído a</th>
                <th>Data de Criação</th>
                <th>Descrição</th> </tr>
        </thead>
        <tbody>';

if (empty($tickets)) {
    $html .= '<tr><td colspan="9" style="text-align: center;">Nenhum ticket encontrado.</td></tr>'; // Colspan ajustado para 9
} else {
    foreach ($tickets as $ticket) {
        $ticket_id_short = substr($ticket['id'], 0, 8) . '...';
        $techs_text = !empty($ticket['techs']) ? implode(', ', $ticket['techs']) : 'Nenhum';
        $created_at = new DateTime($ticket['createdAt']);

        $status_class = '';
        if ($ticket['status'] === 'Aberto') {
            $status_class = 'status-Aberto';
        } elseif ($ticket['status'] === 'Resolvido') {
            $status_class = 'status-Resolvido';
        }

        $html .= '
            <tr>
                <td>' . htmlspecialchars($ticket_id_short) . '</td>
                <td>' . htmlspecialchars($ticket['usuario']) . '</td>
                <td>' . htmlspecialchars($ticket['sector']) . '</td>
                <td>' . htmlspecialchars($ticket['type']) . '</td>
                <td>' . htmlspecialchars($ticket['priority']) . '</td>
                <td><span class="' . $status_class . '">' . htmlspecialchars($ticket['status']) . '</span></td>
                <td>' . htmlspecialchars($techs_text) . '</td>
                <td>' . htmlspecialchars($created_at->format('d/m/Y H:i:s')) . '</td>
                <td>' . htmlspecialchars($ticket['description']) . '</td> </tr>';
    }
}

$html .= '
        </tbody>
    </table>
    <div class="footer">
        Relatório gerado em: ' . date('d/m/Y H:i:s') . '
    </div>
</body>
</html>';

// Configurações do Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); // Permite carregar recursos externos se houver
$options->set('defaultFont', 'DejaVu Sans'); // Define uma fonte padrão que suporta UTF-8

$dompdf = new Dompdf($options);

// Carrega o HTML
$dompdf->loadHtml($html, 'UTF-8'); // Especifica a codificação UTF-8

// (Opcional) Define o tamanho e a orientação do papel
$dompdf->setPaper('A4', 'portrait');

// Renderiza o HTML para PDF
$dompdf->render();

// Envia o PDF para o navegador
$dompdf->stream("relatorio_tickets.pdf", array("Attachment" => true));

exit;