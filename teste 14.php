<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Verificar se o usuário está cadastrado no local sede
$stmt = $dbconn->prepare("SELECT nome FROM unidadehemopa WHERE id = :local_id");
$stmt->execute([':local_id' => $_SESSION['unidade_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['nome'] !== 'sede') {
    $_SESSION['error_message'] = 'Apenas usuários cadastrados no local sede podem receber pacotes.';
    header('Location: index.php');
    exit();
}
    
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigobarras = $_POST['codigobarras'];

    //=B32492480005907    =B32492480005940    =B32492480005941    =B32492480006007    =B32492480006040    A510006133468A  A510006133448A  A510006133498A    B3252510006133498     

    $digitoverificarp = substr($codigobarras, 0, 1);
    $digitoverificaru = substr($codigobarras, -1);

    if ($digitoverificarp == '=' || ctype_digit( $digitoverificaru)) {
        $codigobarras = substr($codigobarras, 1);
    }
    else{
        // Remover o primeiro e o último dígito do código de barras 
        $codigobarras = substr($codigobarras, 1, -1);
    }

    // Extrair o penúltimo dígito do código de barras
    $penultimo_digito = substr($codigobarras, -2, 1);

    // Extrair os dois ultmos dígitos do código de barras
    $doisultimos_digito = substr($codigobarras, -2);

    // Verificar se o pacote tem status "enviado"
    $stmt = $dbconn->prepare("SELECT * FROM pacotes WHERE codigobarras = :codigobarras");
    $stmt->execute([':codigobarras' => $codigobarras]);
    $pacote = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($pacote && $pacote['status'] === "enviado") {

        $stmt = $dbconn->prepare("UPDATE pacotes SET data_recebimento = NOW(), status = 'recebido', usuario_recebimento_id = :usuario_recebimento_id WHERE codigobarras = :codigobarras");
        $stmt->execute([
            ':usuario_recebimento_id' => $_SESSION['user_id'],
            ':codigobarras' => $codigobarras
        ]);

        $mensagem = 'Amostra recebida com sucesso!';
    } else {
        $mensagem = 'A Amostra não está disponível para recebimento ou não existe.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receber Pacote</title>
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container container-custom">
        <h1 class="text-center mb-4" style="color: #28a745;">Receber Amostra</h1>
        <?php if (isset($mensagem)): ?>
            <div class="alert alert-<?php echo strpos($mensagem, 'sucesso') !== false ? 'success' : 'danger'; ?>"><?php echo $mensagem; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="codigobarras" style="color: #28a745;">Código de Barra:</label>
                <input type="text" name="codigobarras" id="codigobarras" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-custom btn-block"><i class="fas fa-inbox"></i>Receber</button>
        </form>
        <div class="text-center mt-3">
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-angle-left"></i> Voltar</a>
        </div>
        <a href="logout.php" class="btn btn-danger btn-lg mt-3"><i class="fas fa-sign-out-alt"></i> Logout</a>
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