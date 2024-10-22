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


// Selecionar usuários logados e suas respectivas unidades
$sql = "SELECT usuarios.nome, usuarios.matricula, user_sessions.login_time, user_sessions.last_activity, unidadehemopa.nome AS unidade 
        FROM user_sessions
        JOIN usuarios ON user_sessions.user_id = usuarios.id
        JOIN unidadehemopa ON user_sessions.unidade_id = unidadehemopa.id";
$stmt = $dbconn->prepare($sql);
$stmt->execute();
$loggedInUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Usuários Logados</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container">
    <div class="text-center mt-3">
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-angle-left"></i> Voltar</a>
            <a href="logout.php" class="btn btn-danger "><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    <h1 class="mt-5">Usuários Logados</h1>
    <table class="table table-striped table-bordered mt-3">
        <thead class="thead-dark">
            <tr>
                <th>Nome</th>
                <th>Matrícula</th>
                <th>Unidade</th>
                <th>Hora de Login</th>
                <th>Última Atividade</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($loggedInUsers as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['nome']); ?></td>
                    <td><?= htmlspecialchars($user['matricula']); ?></td>
                    <td><?= htmlspecialchars($user['unidade']); ?></td>
                    <td><?= htmlspecialchars(date('d-m-Y H:i:s', strtotime($user['login_time']))); ?></td>
                    <td><?= htmlspecialchars(date('d-m-Y H:i:s', strtotime($user['last_activity']))); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
