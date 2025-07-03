<?php
session_start();

if (isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $senha = $_POST['senha'] ?? '';

    // ATENÇÃO: EM UM AMBIENTE DE PRODUÇÃO, ISSO DEVE SER SUBSTITUÍDO POR:
    // 1. Consulta a um banco de dados
    // 2. Verificação de senha usando password_verify() com um hash armazenado
    // Exemplo para um ambiente real (apenas para ilustrar, não implementado aqui):
    // $senhaArmazenadaHash = '$2y$10$XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'; // Hash da senha '1234'
    // if ($usuario === 'admin' && password_verify($senha, $senhaArmazenadaHash)) {
    //     session_regenerate_id(true);
    //     $_SESSION['usuario'] = $usuario;
    //     header("Location: index.php");
    //     exit;
    // }

    // Hardcoded credentials for demonstration ONLY. DO NOT USE IN PRODUCTION.
    if ($usuario === 'admin' && $senha === '1234') {
        // Previne Session Fixation
        session_regenerate_id(true);
        $_SESSION['usuario'] = $usuario;
        header("Location: index.php");
        exit;
    } else {
        $erro = 'Usuário ou senha inválidos.';
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
    input { display: block; margin-bottom: 15px; width: calc(100% - 20px); padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 1em; }
    button { padding: 10px 20px; width: 100%; background: #2ecc71; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 1.1em; transition: background 0.3s ease; }
    button:hover { background: #27ae60; }
    .erro { color: red; margin-top: 10px; font-size: 0.9em; }
  </style>
</head>
<body>
  <form method="POST">
    <h2>Login</h2>
    <input type="text" name="usuario" placeholder="Usuário" required autocomplete="username">
    <input type="password" name="senha" placeholder="Senha" required autocomplete="current-password">
    <button type="submit">Entrar</button>
    <?php if ($erro): ?>
      <p class="erro"><?php echo $erro; ?></p>
    <?php endif; ?>
  </form>
</body>
</html>