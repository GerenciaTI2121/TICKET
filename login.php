<?php
session_start();
require_once 'db_config.php'; // Inclua o arquivo de configuração do banco de dados

// Se o usuário já está logado, redireciona para a página principal
if (isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}

$erro = ''; // Variável para armazenar mensagens de erro

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_digitado = $_POST['usuario'] ?? '';
    $senha_digitada = $_POST['senha'] ?? '';

    // Validação básica de entrada
    if (empty($usuario_digitado) || empty($senha_digitada)) {
        $erro = 'Por favor, preencha todos os campos.';
    } else {
        try {
            // Prepara a consulta SQL para buscar o usuário
            // Usamos prepared statements para prevenir SQL Injection
            $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = :username");
            $stmt->bindParam(':username', $usuario_digitado);
            $stmt->execute();

            // Pega os dados do usuário
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verifica se o usuário existe e se a senha está correta
            if ($user && password_verify($senha_digitada, $user['password_hash'])) {
                // Senha correta! Inicia a sessão.
                session_regenerate_id(true); // Previne Session Fixation
                $_SESSION['usuario'] = $user['username']; // Armazena o nome de usuário na sessão
                $_SESSION['user_id'] = $user['id']; // Opcional: armazena o ID do usuário

                header("Location: index.php"); // Redireciona para a página principal
                exit;
            } else {
                $erro = 'Usuário ou senha inválidos.'; // Mensagem genérica para segurança
            }
        } catch (PDOException $e) {
            error_log("Erro no login (DB): " . $e->getMessage());
            $erro = 'Ocorreu um erro interno ao tentar fazer login. Tente novamente mais tarde.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <style>
    body { font-family: sans-serif; background: #f2f2f2; display: flex; height: 100vh; align-items: center; justify-content: center; margin: 0; }
    form { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 300px; text-align: center; }
    h2 { margin-bottom: 20px; color: #333; }
    .error { color: red; margin-bottom: 15px; } /* Estilo para mensagem de erro */
    input { display: block; margin-bottom: 15px; width: calc(100% - 20px); padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 1em; }
    button { padding: 10px 20px; width: 100%; background: #2ecc71; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 1.1em; transition: background 0.3s ease; }
    button:hover { background: #27ae60; }
  </style>
</head>
<body>
  <form method="POST" action="login.php">
    <h2>Login</h2>
    <?php if ($erro): ?>
      <p class="error"><?php echo htmlspecialchars($erro); ?></p>
    <?php endif; ?>
    <input type="text" name="usuario" placeholder="Nome de Usuário" required autocomplete="username">
    <input type="password" name="senha" placeholder="Senha" required autocomplete="current-password">
    <button type="submit">Entrar</button>
  </form>
</body>
</html>