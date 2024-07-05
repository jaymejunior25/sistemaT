<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $descricao = $_POST['descricao'];
    $codigobarras = $_POST['codigobarras'];
    $usuario_cadastro_id = $_SESSION['user_id'];
    $local_id = $_SESSION['unidade_id'];

        // Inserir o novo pacote no banco de dados
        $stmt = $dbconn->prepare("INSERT INTO pacotes (descricao, codigobarras,  usuario_cadastro_id, unidade_cadastro_id, data_cadastro) VALUES (:descricao, :codigobarras, :usuario_cadastro_id, :unidade_cadastro_id, NOW())");
        $stmt->execute([
            ':descricao' => $descricao,
            ':codigobarras' => $codigobarras,
            ':usuario_cadastro_id' => $usuario_cadastro_id,
            ':unidade_cadastro_id' => $local_id
        ]);
    
        $mensagem = 'Amostra cadastrada com sucesso!';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Pacote</title>
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container container-custom">
        <h1 class="text-center mb-4" style="color: #28a745;">Cadastrar Amostra</h1>
        <?php if (isset($mensagem)): ?>
            <div class="alert alert-success"><?php echo $mensagem; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="descricao" style="color: #28a745;">Descrição:</label>
                <input type="text" name="descricao" id="descricao" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="codigobarras" style="color: #28a745;">Código de Barras:</label>
                <input type="text" name="codigobarras" id="codigobarras" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-custom btn-block"><i class="fas fa-plus"></i>Cadastrar</button>
        </form>
        <div class="text-center mt-3">
            <a href="index.php" class="btn btn-secondary">Voltar</a>
        </div>
        <a href="logout.php" class="btn btn-danger btn-lg mt-3">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
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
