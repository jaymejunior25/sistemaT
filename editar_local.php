<?php
session_start();
include 'db.php';

// Verificar se o usuário está logado e é admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Verificar se o ID do local a ser editado foi passado via GET
if (!isset($_GET['id'])) {
    header('Location: gerenciar_locais.php');
    exit;
}

$local_id = $_GET['id'];

// Buscar dados do local no banco de dados
$stmt = $dbconn->prepare('SELECT * FROM unidadehemopa WHERE id = :id');
$stmt->execute(['id' => $local_id]);
$local = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$local) {
    $_SESSION['error_message'] = 'Local não encontrado.';
    header('Location: gerenciar_locais.php');
    exit;
}

// Atualizar dados do local
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];

    $stmt = $dbconn->prepare('UPDATE unidadehemopa SET nome = :nome WHERE id = :id');
    $stmt->execute([
        'nome' => $nome,
        'id' => $local_id
    ]);

    $_SESSION['success_message'] = 'Local atualizado com sucesso.';
    header('Location: gerenciar_locais.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Local</title>
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Editar Local</h1>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="form-group">
                <label for="nome">Nome do Local</label>
                <input type="text" name="nome" id="nome" class="form-control" value="<?php echo htmlspecialchars($local['nome']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-edit"></i>Salvar Alterações</button>
            <a href="gerenciar_locais.php" class="btn btn-secondary"> <i class="fas fa-angle-left"></i>Voltar</a>
            <a href="logout.php" class="btn btn-danger btn-lg mt-3">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </form>
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
