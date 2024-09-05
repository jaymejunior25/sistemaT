<?php

session_start();
include 'db.php';

// Verificar se o usuário é administrador
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['user_type'] != 'admin') {
    header('Location: index.php');
    exit();
}

// Selecionar usuários logados
$sql = "SELECT usuarios.nome, usuarios.matricula, user_sessions.login_time, user_sessions.last_activity 
        FROM user_sessions 
        JOIN usuarios ON user_sessions.user_id = usuarios.id";
$stmt = $dbconn->prepare($sql);
$stmt->execute();
$loggedInUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Usuários Logados</title>
</head>
<body>
    <h1>Usuários Logados</h1>
    <table border="1">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Email</th>
                <th>Hora de Login</th>
                <th>Última Atividade</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($loggedInUsers as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['nome']); ?></td>
                    <td><?= htmlspecialchars($user['matricula']); ?></td>
                    <td><?= htmlspecialchars($user['login_time']); ?></td>
                    <td><?= htmlspecialchars($user['last_activity']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
