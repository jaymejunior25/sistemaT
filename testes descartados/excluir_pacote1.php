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

// Verificar se o ID do pacote foi passado pela URL
if (!isset($_GET['id'])) {
    header('Location: listar_pacotes.php');
    exit();
}

$pacote_id = $_GET['id'];

// Verificar se o formulário de confirmação de senha foi submetido
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_password'])) {
    // Verificar a senha do usuário
    $stmt = $dbconn->prepare('SELECT senha FROM usuarios WHERE id = :id');
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (password_verify($_POST['confirm_password'], $user['senha'])) {
        // Excluir o pacote do banco de dados
        $stmt = $dbconn->prepare("DELETE FROM pacotes WHERE id = :id");
        $stmt->execute([':id' => $pacote_id]);

        header('Location: lista_pacotes.php');
        exit();
    } else {
        $error_message = 'Senha incorreta. Por favor, tente novamente.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Pacote</title>
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4" style="color: #28a745;">Excluir Pacote</h1>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="confirm_password">Confirme sua Senha</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-danger">Excluir</button>
            <a href="lista_pacotes.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
