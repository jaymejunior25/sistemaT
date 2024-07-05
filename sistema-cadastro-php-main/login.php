<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];

    $stmt = $dbconn->prepare("SELECT * FROM usuarios WHERE usuario = :usuario");
    $stmt->execute(['usuario' => $usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($senha, $user['senha'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['usuario'];
        $_SESSION['user_type'] = $user['tipoconta'];
        $_SESSION['unidade_id'] = $user['unidade_id'];
        header('Location: index.php');
        exit();
    } else {
        echo 'Login falhou!';
    }
}
?>

<form method="POST" action="">
    Login: <input type="text" name="usuario" required>
    Senha: <input type="password" name="senha" required>
    <button type="submit">Login</button>
</form>
