<?php
require_once 'db_config.php'; // Inclui suas configurações de banco de dados

// ATENÇÃO: DEFINA AQUI O USUÁRIO E A SENHA QUE VOCÊ DESEJA PARA O LOGIN NO BANCO DE DADOS
$username_to_add = 'TI'; // O nome de usuário que você quer
$password_to_hash = 'Diamante1872*'; // A senha que você vai usar para este usuário

// Cria um hash seguro da senha
$hashed_password = password_hash($password_to_hash, PASSWORD_DEFAULT);

try {
    // Insere o usuário no banco de dados
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
    $stmt->execute([$username_to_add, $hashed_password]);

    echo "Usuário '$username_to_add' criado com sucesso no banco de dados com a senha hashificada!";
} catch (PDOException $e) {
    if ($e->getCode() == 23000) { // Código de erro para entrada duplicada (UNIQUE constraint)
        echo "Erro: O usuário '$username_to_add' já existe no banco de dados.";
    } else {
        echo "Erro ao criar usuário: " . $e->getMessage();
    }
}
?>