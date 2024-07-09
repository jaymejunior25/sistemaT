<?php
session_start();
include 'db.php';

// Verificar se o usuário está logado e é admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Consultar todos os locais
$query = $dbconn->query('SELECT * FROM unidadehemopa');
$locais = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Locais</title>
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <div class="container container-customlistas">
        <h1 class="text-center">Gerenciar Locais</h1>
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        <a href="cadastrounidade.php" class="btn btn-custom"><i class="fas fa-plus"></i> Adicionar Local</a>
        <div class="table-wrapper">
            <table class="table table-bordered table-hover table-striped">
                <thead class="theadfixed">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($locais as $local): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($local['id']); ?></td>
                            <td><?php echo htmlspecialchars($local['nome']); ?></td>
                            <td>
                                <a href="editar_local.php?id=<?php echo $local['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Editar</a>
                                <a href="excluir_local.php?id=<?php echo $local['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este local?');"><i class="fas fa-trash"></i> Excluir</a>
                                </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <a href="index.php" class="btn btn-secondary"> <i class="fas fa-angle-left"></i>Voltar</a>
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
