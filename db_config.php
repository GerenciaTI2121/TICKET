<?php
// db_config.php
$dbHost = 'localhost';
$dbName = 'ticket_db';
// AS CREDENCIAIS ABAIXO SÃO PARA TESTE NO XAMPP COM USUÁRIO PADRÃO 'root' SEM SENHA.
// QUANDO FOR PARA O SERVIDOR DE PRODUÇÃO, VOCÊ DEVE MUDAR ISTO PARA SEU USUÁRIO E SENHA SEGUROS.
$dbUser = 'admin';        // <--- MUITO IMPORTANTE: USE 'root' para XAMPP
$dbPass = 'Diamante1872*';            // <--- MUITO IMPORTANTE: USE '' (string vazia) para XAMPP

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // É uma boa prática logar o erro detalhado no servidor para depuração.
    error_log('Erro de conexão com o DB: ' . $e->getMessage());

    // --- ESTE É O BLOCO CRÍTICO QUE PRECISA RETORNAR JSON ---
    header('Content-Type: application/json'); // Informa ao navegador que a resposta é JSON
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor. Não foi possível conectar ao banco de dados.'
        // Evite exibir $e->getMessage() diretamente para o usuário em um ambiente de produção por segurança.
    ]);
    exit; // Impede que qualquer outro código PHP seja executado e potencialmente polua a saída JSON.
    // --------------------------------------------------------
}
?>