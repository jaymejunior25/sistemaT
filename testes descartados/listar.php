<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$query = $dbconn->query("SELECT * FROM usuarios");
$usuarios = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar Usuários</title>
</head>
<body>
    <h1>Usuários Cadastrados</h1>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Matricula</th>
            <th>Usuario</th>
            <th>Unidade</th>
            <th>Tipo Conta</th>
            <th>Ações</th>
        </tr>
        <?php foreach ($usuarios as $usuario): ?>
        <tr>
            <td><?php echo $usuario['id']; ?></td>
            <td><?php echo $usuario['nome']; ?></td>
            <td><?php echo $usuario['matricula']; ?></td>
            <td><?php echo $usuario['usuario']; ?></td>
            <td><?php echo $usuario['unidade']; ?></td>
            <td><?php echo $usuario['tipoconta']; ?></td>
            <td>
                <a href="editar.php?id=<?php echo $usuario['id']; ?>">Editar</a>
                <a href="excluir.php?id=<?php echo $usuario['id']; ?>">Excluir</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <a href="cadastro.php">Adicionar Usuário</a>
    <a href="index.php">Voltar</a>
</body>
</html>