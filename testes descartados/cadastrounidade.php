<?php
session_start();
include 'db.php';

/*if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}*/

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];

    $stmt = $dbconn->prepare("INSERT INTO unidadehemopa (nome) VALUES (:nome)");
    $stmt->execute(['nome' => $nome]);

    echo 'Local cadastrado com sucesso!';
}
?>

<form method="POST" action="">
    Nome da Unidade: <input type="text" name="nome" required>
    <button type="submit">Cadastrar Local</button>
</form>

<a href="index.php">Voltar</a>