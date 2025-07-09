<?php
// migrate_tickets.php

// -----------------------------------------------------------
// 1. Configurações de Conexão com o Banco de Dados MySQL
//    ATENÇÃO: Substitua 'seu_usuario_app' e 'sua_senha_app_forte'
//             pelas credenciais do usuário que você criou para sua aplicação no MySQL.
// -----------------------------------------------------------
$dbHost = 'localhost'; // Geralmente 'localhost' se o MySQL estiver no mesmo servidor que o PHP
$dbName = 'ticket_db'; // O nome do banco de dados que você criou
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
    // Conecta ao banco de dados usando PDO (PHP Data Objects)
    // PDO é a forma recomendada para interagir com bancos de dados em PHP.
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Define o modo de erro para lançar exceções
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Retorna os resultados como arrays associativos

    echo "Conexão com o banco de dados MySQL estabelecida com sucesso.\n";

    // -----------------------------------------------------------
    // 3. Lê o arquivo JSON existente
    // -----------------------------------------------------------
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

    // -----------------------------------------------------------
    // 4. Itera sobre cada ticket e insere/atualiza no banco de dados
    // -----------------------------------------------------------
    foreach ($tickets as $ticket) {
        try {
            // Prepare os dados para inserção, fornecendo valores padrão se algum campo estiver faltando
            // Isso ajuda a evitar erros se o JSON não for 100% consistente.
            $id = $ticket['id'] ?? uniqid('ticket_', true); // Gera um ID único se o ticket não tiver um
            $usuario = $ticket['usuario'] ?? 'Usuário Desconhecido';
            $sector = $ticket['sector'] ?? 'Setor Não Informado';
            $type = $ticket['type'] ?? 'Tipo Não Especificado';
            $priority = $ticket['priority'] ?? 'Baixa';
            $description = $ticket['description'] ?? 'Sem descrição.';
            $status = $ticket['status'] ?? 'Aberto'; // Status padrão 'Aberto'

            // Validação de status para ENUM: assegura que o status é um dos valores permitidos
            $allowedStatuses = ['Aberto', 'Resolvido', 'Arquivado'];
            if (!in_array($status, $allowedStatuses)) {
                $status = 'Aberto'; // Fallback para status padrão se for inválido
                echo "AVISO: Status inválido para o ticket ID '$id'. Definindo status para 'Aberto'.\n";
            }

            // Armazena o array de técnicos como uma string JSON para a coluna 'techs' (tipo JSON no MySQL)
            $techs = json_encode($ticket['techs'] ?? []);

            // Formata a data de criação para o formato DATETIME do MySQL (YYYY-MM-DD HH:MM:SS)
            $createdAt = $ticket['createdAt'] ?? date('Y-m-d H:i:s');
            // Tenta converter para DateTime se for uma string (pode ser "2023-01-01 10:00:00" ou timestamp)
            try {
                $dt = new DateTime($createdAt);
                $createdAt = $dt->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                $createdAt = date('Y-m-d H:i:s'); // Fallback para data/hora atual se a data for inválida
                echo "AVISO: Data/hora de criação inválida para o ticket ID '$id'. Usando a data/hora atual. Detalhes: " . $e->getMessage() . "\n";
            }

            // Query de inserção com ON DUPLICATE KEY UPDATE
            // Isso significa: se um ticket com o mesmo 'id' já existir, ele será atualizado;
            // caso contrário, um novo ticket será inserido.
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
                    updatedAt = NOW() -- Atualiza updatedAt na modificação
            ");

            // Executa a query com os valores parametrizados (protege contra SQL Injection)
            $stmt->execute([
                'id' => $id,
                'usuario' => $usuario,
                'sector' => $sector,
                'type' => $type,
                'priority' => $priority,
                'description' => $description,
                'status' => $status,
                'techs' => $techs, // Já é uma string JSON
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