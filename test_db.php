<?php
// test_db.php - Script para testar a conexão com o banco de dados MySQL

// --------------------------------------------------------------------------
// ATENÇÃO: Configure estas variáveis com as credenciais do seu banco de dados
//          Para XAMPP padrão, o usuário é 'root' e a senha é '' (vazia)
// --------------------------------------------------------------------------
$dbHost = 'localhost';  // Geralmente 'localhost' para XAMPP
$dbName = 'ticket_db';  // O nome do banco de dados que você criou (ticket_db)
$dbUser = 'root';       // O usuário do banco de dados (provavelmente 'root' no XAMPP)
$dbPass = '';           // A senha do banco de dados (provavelmente vazia '' no XAMPP)

// Tentar estabelecer a conexão PDO
try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    // Definir o modo de erro para PDO para que as exceções sejam lançadas
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h1>Conexão com o banco de dados MySQL bem-sucedida!</h1>";
    echo "<p>Parabéns! Isso significa que:</p>";
    echo "<ul>";
    echo "<li>Seu Apache e MySQL estão rodando.</li>";
    echo "<li>A extensão PHP PDO MySQL está habilitada.</li>";
    echo "<li>As credenciais de conexão em `test_db.php` estão corretas.</li>";
    echo "<li>O banco de dados '$dbName' existe e está acessível.</li>";
    echo "</ul>";

} catch (PDOException $e) {
    // Se a conexão falhar, capturar a exceção e exibir uma mensagem de erro
    echo "<h1>Erro de conexão com o banco de dados:</h1>";
    echo "<p>Não foi possível conectar ao MySQL. Verifique o seguinte:</p>";
    echo "<ul>";
    echo "<li>O serviço MySQL está rodando no XAMPP Control Panel?</li>";
    echo "<li>As credenciais (\$dbHost, \$dbName, \$dbUser, \$dbPass) em `test_db.php` estão corretas?</li>";
    echo "<li>A extensão `pdo_mysql` está habilitada no seu `php.ini`? (Descomente `;extension=pdo_mysql` e reinicie o Apache).</li>";
    echo "<li>Você criou o banco de dados `$dbName` no phpMyAdmin?</li>";
    echo "<li>Não há firewalls bloqueando a conexão na porta 3306 (padrão do MySQL)?</li>";
    echo "</ul>";
    echo "<p><strong>Detalhes do Erro:</strong> " . $e->getMessage() . "</p>";
    error_log("Erro no test_db.php: " . $e->getMessage()); // Para logs do servidor
}
?>