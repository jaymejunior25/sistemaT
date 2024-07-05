<?php
session_start();
include 'db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Processar o formulário quando for enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $descricao = $_POST['descricao'];
    $usuario_envio_id = $_SESSION['user_id'];
    $local_id = $_SESSION['unidade_id'];
 
    // Inserir o novo pacote no banco de dados
    $stmt = $dbconn->prepare("INSERT INTO pacotes (descricao, status, usuario_envio_id, unidade_envio_id, data_envio) VALUES (:descricao, 'enviado', :usuario_envio_id, :unidade_envio_id, NOW())");
    $stmt->execute([
        ':descricao' => $descricao,
        ':usuario_envio_id' => $usuario_envio_id,
        ':unidade_envio_id' => $local_id
    ]);

    echo 'Pacote cadastrado com sucesso!';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cadastrar Pacote</title>
</head>
<body>
    <h1>Cadastrar Pacote</h1>
    <form method="POST" action="">
        <label for="descricao">Descrição:</label>
        <input type="text" name="descricao" id="descricao" required><br><br>

        <button type="submit">Cadastrar</button>
    </form>
    <a href="index.php">Voltar</a>
</body>
</html>

