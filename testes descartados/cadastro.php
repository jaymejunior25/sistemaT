<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $matricula = $_POST['matricula'];
    $usuario = $_POST['usuario'];
    $senha = password_hash($_POST['senha'], PASSWORD_BCRYPT);
    $unidade = $_POST['unidade'];
    $tipo = $_POST['tipoConta'];

    $query = $dbconn->prepare("INSERT INTO usuarios (nome, matricula, usuario, senha, unidade, tipoConta) VALUES (:nome, :matricula, :usuario, :senha, :unidade, :tipoConta)");
    $query->bindParam(':nome', $nome);
    $query->bindParam(':matricula', $matricula);
    $query->bindParam(':usuario', $usuario);
    $query->bindParam(':senha', $senha);
    $query->bindParam(':unidade', $unidade);
    $query->bindParam(':tipoConta', $tipo);

    if ($query->execute()) {
        echo "Cadastro realizado com sucesso!";
    } else {
        echo "Erro no cadastro.";
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Usuários</title>
</head>
<body>
<title>Cadastro de Usuários</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            width: 50%;
            margin: auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        .form-group button {
            padding: 10px 15px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .form-group button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Cadastro de Usuários</h1>
        <form action="cadastro.php" method="post">
            <div class="form-group">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" required>
            </div>
            <div class="form-group">
                <label for="matricula">matricula:</label>
                <input type="text" id="matricula" name="matricula" required>
            </div>
            <div class="form-group">
                <label for="usuario">Usuario:</label>
                <input type="text" id="usuario" name="usuario" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            <div class="form-group">
                <label for="unidade">Unidade:</label>
                <input type="text" id="unidade" name="unidade" required>
            </div>
            <div class="form-group">
                <label for="tipoConta">Tipo da Conta:</label>
                <input type="text" id="tipoConta" name="tipoConta" required>
            </div>
            <div class="form-group">
                <button type="submit">Cadastrar</button>
            </div>
        </form>
    </div>
    <a href="login.php">Login</a>
</body>
</html>
