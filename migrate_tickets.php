<?php
// migrate_tickets.php

// -----------------------------------------------------------
// 1. Configurações de Conexão com o Banco de Dados MySQL
//    ATENÇÃO: Substitua 'seu_usuario_app' e 'sua_senha_app_forte'
//             pelas credenciais do usuário que você criou para sua aplicação no MySQL.
// -----------------------------------------------------------
$dbHost = 'localhost';
$dbName = 'ticket_db';
$dbUser = 'admin'; // <<< PREENCHA AQUI SEU USUÁRIO DO BANCO DE DADOS >>>
$dbPass = 'Diamante1872*'; // <<< PREENCHA AQUI SUA SENHA DO BANCO DE DADOS >>>

// -----------------------------------------------------------
// 2. Caminho para o arquivo JSON de tickets original
//    Certifique-se de que este caminho está correto e o PHP tem permissão de leitura.
// -----------------------------------------------------------
$jsonFile = 'tickets.json';

echo "Iniciando script de migração...\n";
echo "Tentando conectar ao MySQL ($dbHost, DB: $dbName, Usuário: $dbUser)...\n";

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    echo "Conexão com o banco de dados MySQL estabelecida com sucesso.\n";

    if (!file_exists($jsonFile)) {
        die("ERRO: O arquivo JSON '$jsonFile' não foi encontrado. Verifique o caminho e o nome do arquivo.\n");
    }

    $jsonData = file_get_contents($jsonFile);
    $tickets = json_decode($jsonData, true);

    if (!is_array($tickets) || empty($tickets)) {
        echo "AVISO: Nenhum ticket encontrado no arquivo JSON ou o arquivo JSON está vazio/inválido.\n";
        echo "Nenhuma migração de dados será realizada.\n";
        exit;
    }

    echo "Total de " . count($tickets) . " tickets encontrados no arquivo JSON. Iniciando importação...\n";

    $importedCount = 0;
    $errorsCount = 0;

    foreach ($tickets as $ticket) {
        try {
            $id = $ticket['id'] ?? uniqid('ticket_', true);
            $usuario = $ticket['usuario'] ?? 'Usuário Desconhecido';
            $sector = $ticket['sector'] ?? 'Setor Não Informado';
            $type = $ticket['type'] ?? 'Tipo Não Especificado';
            $priority = $ticket['priority'] ?? 'Baixa';
            $description = $ticket['description'] ?? 'Sem descrição.';
            $status = $ticket['status'] ?? 'Aberto';

            $allowedStatuses = ['Aberto', 'Resolvido', 'Arquivado'];
            if (!in_array($status, $allowedStatuses)) {
                $status = 'Aberto';
                echo "AVISO: Status inválido para o ticket ID '$id'. Definindo status para 'Aberto'.\n";
            }

            $techs = json_encode($ticket['techs'] ?? []);

            $createdAt = $ticket['createdAt'] ?? date('Y-m-d H:i:s');
            try {
                $dt = new DateTime($createdAt);
                $createdAt = $dt->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                $createdAt = date('Y-m-d H:i:s');
                echo "AVISO: Data/hora de criação inválida para o ticket ID '$id'. Usando a data/hora atual. Detalhes: " . $e->getMessage() . "\n";
            }

            $stmt = $pdo->prepare("
                INSERT INTO tickets (id, usuario, sector, type, priority, description, status, techs, createdAt)
                VALUES (:id, :usuario, :sector, :type, :priority, :description, :status, :techs, :createdAt)
                ON DUPLICATE KEY UPDATE
                    usuario = VALUES(usuario),
                    sector = VALUES(sector),
                    type = VALUES(type),
                    priority = VALUES(priority),
                    description = VALUES(description),
                    status = VALUES(status),
                    techs = VALUES(techs),
                    createdAt = VALUES(createdAt),
                    updatedAt = NOW()
            ");

            $stmt->execute([
                'id' => $id,
                'usuario' => $usuario,
                'sector' => $sector,
                'type' => $type,
                'priority' => $priority,
                'description' => $description,
                'status' => $status,
                'techs' => $techs,
                'createdAt' => $createdAt
            ]);

            $importedCount++;
            echo "Ticket ID '$id' importado/atualizado com sucesso.\n";

        } catch (PDOException $e) {
            $errorsCount++;
            echo "ERRO ao importar ticket ID '" . ($ticket['id'] ?? 'N/A') . "': " . $e->getMessage() . "\n";
        }
    }

    echo "---------------------------------------------------\n";
    echo "MIGRAÇÃO CONCLUÍDA.\n";
    echo "Total de tickets processados no JSON: " . count($tickets) . "\n";
    echo "Tickets importados/atualizados no MySQL: $importedCount\n";
    echo "Tickets com erros durante a importação: $errorsCount\n";
    echo "---------------------------------------------------\n";

} catch (PDOException $e) {
    echo "ERRO FATAL: Falha na conexão com o banco de dados ou erro de SQL crítico: " . $e->getMessage() . "\n";
    echo "Verifique as configurações de conexão (\$dbHost, \$dbName, \$dbUser, \$dbPass).\n";
    echo "Certifique-se de que o MySQL está rodando e a porta 3306 está acessível.\n";
} catch (Exception $e) {
    echo "ERRO FATAL: Erro geral no script: " . $e->getMessage() . "\n";
}

?>