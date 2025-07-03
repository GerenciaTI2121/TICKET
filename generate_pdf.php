<?php
session_start();

// Proteger para que apenas o admin possa gerar relatórios em PDF
if (!isset($_SESSION['usuario']) || $_SESSION['usuario'] !== 'admin') {
    die('Acesso negado. Apenas administradores podem gerar relatórios em PDF.');
}

require_once 'vendor/autoload.php'; // Carrega o autoloader do Composer (Dompdf)

use Dompdf\Dompdf;
use Dompdf\Options;

// --- Função para limpar dados (a mesma que você já tem) ---
function limpar($str) {
    return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
}
// --- Fim da função de limpeza ---

$file = 'tickets.json';
$tickets = [];

if (file_exists($file)) {
    $tickets = json_decode(file_get_contents($file), true);
    if (!is_array($tickets)) {
        $tickets = [];
    }
}

if (empty($tickets)) {
    die('Nenhum ticket encontrado para gerar o relatório.');
}

// Ordenar tickets pelo mais recente primeiro (opcional, igual ao get_report.php)
usort($tickets, function($a, $b) {
    return strtotime($b['createdAt']) - strtotime($a['createdAt']);
});


// Gerar o HTML para o PDF
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Relatório Geral de Tickets</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; margin: 20px; }
        h1 { text-align: center; color: #2ecc71; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; color: #333; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .status-Aberto { color: #f39c12; font-weight: bold; }
        .status-Resolvido { color: #27ae60; font-weight: bold; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8px; color: #777; }
    </style>
</head>
<body>
    <h1>Relatório Geral de Tickets</h1>
    <p>Gerado em: ' . date('d/m/Y H:i:s') . '</p>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuário</th>
                <th>Setor</th>
                <th>Tipo</th>
                <th>Prioridade</th>
                <th>Status</th>
                <th>Atribuído a</th>
                <th>Criado Em</th>
            </tr>
        </thead>
        <tbody>';

foreach ($tickets as $ticket) {
    // Aplicar a função limpar para garantir que os dados do JSON sejam seguros para HTML no PDF
    $id = limpar(substr($ticket['id'], 0, 8)) . '...';
    $usuario_limpo = limpar($ticket['usuario']);
    $sector_limpo = limpar($ticket['sector']);
    $type_limpo = limpar($ticket['type']);
    $priority_limpo = limpar($ticket['priority']);
    $status_limpo = limpar($ticket['status']);
    $techs_text = implode(', ', array_map('limpar', $ticket['techs'])); // Limpa cada tech individualmente
    $createdAt_formatado = date('d/m/Y H:i', strtotime($ticket['createdAt']));

    $status_class = '';
    if ($status_limpo === 'Aberto') {
        $status_class = 'status-Aberto';
    } elseif ($status_limpo === 'Resolvido') {
        $status_class = 'status-Resolvido';
    }

    $html .= '
            <tr>
                <td>' . $id . '</td>
                <td>' . $usuario_limpo . '</td>
                <td>' . $sector_limpo . '</td>
                <td>' . $type_limpo . '</td>
                <td>' . $priority_limpo . '</td>
                <td><span class="' . $status_class . '">' . $status_limpo . '</span></td>
                <td>' . $techs_text . '</td>
                <td>' . $createdAt_formatado . '</td>
            </tr>';
}

$html .= '
        </tbody>
    </table>
    <div class="footer">Página <span class="page-number"></span> de <span class="total-pages"></span></div>
</body>
</html>';

// Configurar Dompdf
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans'); // Define uma fonte que suporte caracteres UTF-8
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); // Se tiver imagens externas, etc.

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);

// (Opcional) Configurar o tamanho do papel e a orientação (Portrait = retrato, Landscape = paisagem)
$dompdf->setPaper('A4', 'landscape'); // A4 paisagem é bom para tabelas largas

// Renderizar o HTML como PDF
$dompdf->render();

// Adicionar números de página (opcional)
$dompdf->getCanvas()->page_text(550, 780, "{PAGE_NUM} de {PAGE_COUNT}", null, 8, array(0,0,0));


// Enviar o PDF para o navegador
$dompdf->stream("relatorio_tickets_" . date('Ymd_His') . ".pdf", array("Attachment" => true)); // "Attachment" => true força o download
?>