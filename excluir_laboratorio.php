<?php
session_start();
include 'db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['user_type'] != 'admin') {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $senha_confirmacao = $_POST['senha_confirmacao'];

    // Verificar a senha do usuário
    $stmt = $dbconn->prepare("SELECT senha FROM usuarios WHERE id = :id");
    $stmt->bindParam(':id', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($senha_confirmacao, $user['senha'])) {
        // Deletar o pacote
        $stmt = $dbconn->prepare("DELETE FROM laboratorio WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $mensagem = 'Laboratório excluído com sucesso!';

        header('Location: gerenciar_lab.php');
        exit();
    } else {
        echo "Senha incorreta!";
    }
}
