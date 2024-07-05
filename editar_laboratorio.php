
<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Buscar laboratório pelo ID
    $stmt = $dbconn->prepare("SELECT * FROM laboratorio WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $laboratorio = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$laboratorio) {
        header('Location: gerenciar_laboratorios.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $digito = $_POST['digito'];
    $nome = $_POST['nome'];

    // Atualizar laboratório no banco de dados
    $stmt = $dbconn->prepare("UPDATE laboratorio SET digito = :digito, nome = :nome WHERE id = :id");
    $stmt->execute([
        ':digito' => $digito,
        ':nome' => $nome,
        ':id' => $id
    ]);

    header('Location: gerenciar_laboratorios.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Laboratório</title>
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container container-custom">
        <h1 class="text-center mb-4" style="color: #28a745;">Editar Laboratório</h1>
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($laboratorio['id']); ?>">
            <div class="form-group">
                <label for="digito" style="color: #28a745;">Dígito:</label>
                <input type="text" name="digito" id="digito" class="form-control" value="<?php echo htmlspecialchars($laboratorio['digito']); ?>" required>
            </div>
            <div class="form-group">
                <label for="nome" style="color: #28a745;">Nome:</label>
                <input type="text" name="nome" id="nome" class="form-control" value="<?php echo htmlspecialchars($laboratorio['nome']); ?>" required>
            </div>
            <button type="submit" class="btn btn-custom btn-block"><i class="fas fa-save"></i> Salvar</button>
        </form>
        <div class="text-center mt-3">
            <a href="gerenciar_lab.php" class="btn btn-secondary"><i class="fas fa-angle-left"></i> Voltar</a>
        </div>
        <a href="logout.php" class="btn btn-danger btn-lg mt-3"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    <div class="fixed-bottom toggle-footer cursor_to_down" id="footer_fixed" >
        <div class="fixed-bottom border-top bg-light text-center footer-content p-2" style="z-index:4; ">
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
        function openDeleteModal(labId) {
            $('#pacoteIdToDelete').val(labId);
            $('#confirmPasswordModal').modal('show');
        }
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
