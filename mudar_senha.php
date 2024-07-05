<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $senha_atual = $_POST['senha_atual'];
    $nova_senha = $_POST['nova_senha'];
    $confirmar_nova_senha = $_POST['confirmar_nova_senha'];

    // Verificar se a nova senha e a confirmação da nova senha são iguais
    if ($nova_senha !== $confirmar_nova_senha) {
        $_SESSION['error_message'] = 'A nova senha e a confirmação da nova senha não correspondem.';
    } else {
        // Verificar se a senha atual está correta
        $stmt = $dbconn->prepare("SELECT senha FROM usuarios WHERE id = :id");
        $stmt->execute([':id' => $user_id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($senha_atual, $usuario['senha'])) {

                // Validar senha
            if (strlen($_POST['nova_senha']) < 6 || !preg_match('/[A-Za-z]/', $_POST['nova_senha']) || !preg_match('/\d/', $_POST['nova_senha'])) {
                $_SESSION['error_message'] = 'A senha deve ter pelo menos 6 caracteres e incluir números e letras.';
            } elseif ($_POST['nova_senha'] !== $confirmar_nova_senha) {
                $_SESSION['error_message'] = 'As senhas não correspondem.';
            } else {
                // Atualizar a senha no banco de dados
                $nova_senha_hash = password_hash($nova_senha, PASSWORD_BCRYPT);
                $stmt = $dbconn->prepare("UPDATE usuarios SET senha = :nova_senha WHERE id = :id");
                $stmt->execute([':nova_senha' => $nova_senha_hash, ':id' => $user_id]);

                $_SESSION['success_message'] = 'Senha alterada com sucesso!';
            }

        } else {
            $_SESSION['error_message'] = 'A senha atual está incorreta.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mudar Senha</title>
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container container-custom">
        <h1 class="text-center mb-4" style="color: #28a745;">Mudar Senha</h1>
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="senha_atual" style="color: #28a745;">Senha Atual:</label>
                <input type="password" name="senha_atual" id="senha_atual" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="nova_senha" style="color: #28a745;">Nova Senha:</label>
                <input type="password" name="nova_senha" id="nova_senha" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="confirmar_nova_senha" style="color: #28a745;">Confirmar Nova Senha:</label>
                <input type="password" name="confirmar_nova_senha" id="confirmar_nova_senha" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-custom btn-block"><i class="fas fa-key"></i> Alterar Senha</button>
        </form>
        <div class="text-center mt-3">
            <a href="index.php" class="btn btn-secondary">  <i class="fas fa-angle-left"></i> Voltar</a>
        </div>
    </div>
    <div class="fixed-bottom toggle-footer cursor_to_down" id="footer_fixed" >
            <!-- style="margin-top:50px;" -->
            <div class="fixed-bottom border-top bg-light text-center footer-content p-2" style="z-index:4; ">
                <!-- w3-card  -->
                <div class="footer-text" >
                    Desenvolvido com &#128151; por Gerencia de Informatica - GETIN <br>
                    <a class="text-reset fw-bold" href="http://www.hemopa.pa.gov.br/site/">© Todos os direitos reservados 2024 Hemopa.</a>
                </div>
            </div>
        </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Função para monitorar inatividade
        let inactivityTime = function () {
            let time;
            window.onload = resetTimer;
            document.onmousemove = resetTimer;
            document.onkeypress = resetTimer;
            document.onscroll = resetTimer;
            document.onclick = resetTimer;

            function logout() {
                alert("Você foi desconectado devido à inatividade.");
                window.location.href = 'logout.php';
            }

            function resetTimer() {
                clearTimeout(time);
                time = setTimeout(logout, 900000);  // Tempo em milissegundos 900000 = (15 minutos)
            }
        };

        inactivityTime();
    </script>
</body>
</html>
