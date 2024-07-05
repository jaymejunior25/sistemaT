<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Buscar todos os laboratórios
$stmt = $dbconn->prepare("SELECT * FROM laboratorio");
$stmt->execute();
$laboratorios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Laboratórios</title>
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container container-custom">
        <h1 class="text-center mb-4" style="color: #28a745;">Gerenciar Laboratórios</h1>
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        <a href="cadastro_lab.php" class="btn btn-custom"><i class="fas fa-plus"></i> Adicionar Laboratório</a>
        <div class="table-wrapper" style="position: relative;" id="managerTable">  
            <table class="table table-bordered table-hover table-striped">
                <thead class="theadfixed">
                    <tr>
                        <th>Dígito</th>
                        <th>Nome</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($laboratorios as $laboratorio): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($laboratorio['digito']); ?></td>
                            <td><?php echo htmlspecialchars($laboratorio['nome']); ?></td>
                            <td>
                                <a href="editar_laboratorio.php?id=<?php echo $laboratorio['id']; ?>" class="btn btn-primary btn-sm"> <i class="fas fa-edit"></i>Editar</a>
                                <button type="button"  class="btn btn-danger btn-sm" onclick="openDeleteModal(<?php echo $laboratorio['id']; ?>)"> <i class="fas fa-trash"></i>Excluir</button>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
            <div class="text-center mt-3">
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-angle-left"></i> Voltar</a>
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
        <!-- Modal de Confirmação de Senha -->
        <div class="modal fade" id="confirmPasswordModal" tabindex="-1" role="dialog" aria-labelledby="confirmPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmPasswordModalLabel">Confirmação de Senha</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="deleteForm" method="POST" action="excluir_laboratorio.php">
                        <input type="hidden" name="id" id="pacoteIdToDelete">
                        <div class="form-group">
                            <label for="senha_confirmacao">Digite sua senha:</label>
                            <input type="password" class="form-control" id="senha_confirmacao" name="senha_confirmacao" required>
                        </div>
                        <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                    </form>
                </div>
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
