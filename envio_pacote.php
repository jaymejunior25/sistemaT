<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$local_envio_id = $_SESSION['unidade_id'];
$status_cadastro = 'cadastrado';
$pacotes = [];

// Processar o envio dos pacotes
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_password']) && isset($_POST['pacotes'])) {
    $pacotes_selecionados = $_POST['pacotes'];
    $stmt = $dbconn->prepare('SELECT senha FROM usuarios WHERE id = :id');
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (password_verify($_POST['confirm_password'], $user['senha'])) {
        $stmt = $dbconn->prepare("UPDATE pacotes SET status = 'enviado', data_envio = NOW(), unidade_envio_id = :unidade_envio_id, usuario_envio_id = :usuario_envio_id WHERE unidade_cadastro_id = :unidade_cadastro_id AND status = 'cadastrado'");
        $stmt->execute([
            ':unidade_envio_id' => $_SESSION['unidade_id'],
            ':usuario_envio_id' => $_SESSION['user_id'],
            ':unidade_cadastro_id' => $local_envio_id
        ]);
        $_SESSION['success_message'] = 'Pacotes enviados com sucesso.';
    } else {
        $_SESSION['error_message'] = 'Senha incorreta. Por favor, tente novamente.';
    }
}

// Obter a lista de pacotes cadastrados para o local do usuário
$stmt = $dbconn->prepare("SELECT p.id, p.status, p.codigobarras, p.descricao,  p.data_cadastro, l_lab.nome AS lab_nome,
                        u_cadastro.usuario AS cadastrado_por, l_cadastro.nome AS cadastro_nome  
                        FROM pacotes p 
                        LEFT JOIN usuarios u_cadastro ON p.usuario_cadastro_id = u_cadastro.id 
                        LEFT JOIN unidadehemopa l_cadastro ON p.unidade_cadastro_id = l_cadastro.id 
                        LEFT JOIN laboratorio l_lab ON p.lab_id = l_lab.id
                        WHERE unidade_cadastro_id = :unidade_cadastro_id AND status = :status_cadastro ");
$stmt->execute(['unidade_cadastro_id' => $local_envio_id, 'status_cadastro' => $status_cadastro]);
$pacotes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular o total de pacotes
$totalPacotes = count($pacotes);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Pacote</title>
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <div class="container container-customlistas">
        <h1 class="text-center mb-4">Enviar Amostras</h1>
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
        <div class="mb-3 text-center">
            <h4>Total de Amostras: <?php echo $totalPacotes; ?></h4> <!-- Total de Pacotes -->
        </div>
        <form method="POST" action="" onsubmit="return confirmAction(event)">
            <div class="form-group">
                <label for="pacotes">Pacotes Cadastrados:</label>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead class="theadfixed">
                            <tr>
                                <th>Descrição</th>
                                <th>Código de Barras</th>
                                <th>Laboratório</th>
                                <th>Data de Cadastro</th>
                                <th>Cadastrado por</th>
                                <th>Local de Cadastro</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pacotes as $pacote): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($pacote['descricao']); ?></td>
                                    <td><?php echo htmlspecialchars($pacote['codigobarras']); ?></td>
                                    <td><?php echo htmlspecialchars($pacote['lab_nome']); ?></td>
                                    <td><?php echo htmlspecialchars(date("d-m-Y", strtotime($pacote['data_cadastro']))); ?></td>
                                    <td><?php echo htmlspecialchars($pacote['cadastrado_por']); ?></td>
                                    <td><?php echo htmlspecialchars($pacote['cadastro_nome']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <button type="submit" class="btn btn-custom btn-block"><i class="fas fa-paper-plane"></i> Enviar</button>
        </form>
        <div class="text-center mt-3">
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-angle-left"></i> Voltar</a>
        </div>
        <a href="logout.php" class="btn btn-danger btn-lg mt-3"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    <!-- Modal de Confirmação -->
    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirme sua Senha</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="confirmForm" method="post" action="">
                        <input type="hidden" name="pacotes[]" id="hidden_pacotes">
                        <div class="form-group">
                            <label for="confirm_password">Senha</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Confirmar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="fixed-bottom toggle-footer cursor_to_down" id="footer_fixed">
        <div class="fixed-bottom border-top bg-light text-center footer-content p-2" style="z-index:4;">
            <div class="footer-text">
                Desenvolvido com &#128151; por Gerencia de Informatica - GETIN <br>
                <a class="text-reset fw-bold" href="http://www.hemopa.pa.gov.br/site/">© Todos os direitos reservados 2024 Hemopa.</a>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function confirmAction(event) {
            event.preventDefault();
            var pacotes = [];
            document.querySelectorAll('input[name="pacotes[]"]:checked').forEach(function(checkbox) {
                pacotes.push(checkbox.value);
            });
            //if (pacotes.length === 0) {
            //    alert('Por favor, selecione pelo menos um pacote.');
            //    return false;
            //}
            document.getElementById('hidden_pacotes').value = JSON.stringify(pacotes);
            $('#confirmModal').modal('show');
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
