
<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['user_type'] != 'admin') {
    header('Location: index.php');
    exit();
}

// Obter a lista de locais para exibir no formulário
$stmt = $dbconn->prepare("SELECT id, nome FROM unidadehemopa");
$stmt->execute();
$locais = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verificar a senha do usuário logado
    $user_id = $_SESSION['user_id'];
    $senha_usuario = $_POST['senha_usuario'];



    $stmt = $dbconn->prepare("SELECT senha FROM usuarios WHERE id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);


    

    if (password_verify($senha_usuario, $usuario['senha'])) {
        // Senha correta, processar o formulário de cadastro de usuário
        $nome = $_POST['nome'];
        $matricula = $_POST['matricula'];
        $usuario = $_POST['usuario'];
        $senha = password_hash($_POST['senha'], PASSWORD_BCRYPT); // Hash da senha
        $tipo = $_POST['tipoconta'];
        $local_id = $_POST['unidadehemopa_id'];
        $confirmar_senha = $_POST['confirmar_senha'];
        $tipo_usuario = $_POST['tipo_usuario'];
        
        // Validar senha
        if (strlen($senha) < 6 || !preg_match('/[A-Za-z]/', $senha) || !preg_match('/\d/', $senha)) {
            $_SESSION['error_message'] = 'A senha deve ter pelo menos 6 caracteres e incluir números e letras.';
        } elseif ($senha !== $confirmar_senha) {
            $_SESSION['error_message'] = 'As senhas não correspondem.';
        } else {
            // Inserir o novo usuário no banco de dados
            $stmt = $dbconn->prepare("INSERT INTO usuarios (nome, senha, matricula, tipoconta, unidade_id, usuario) VALUES (:nome, :senha, :matricula, :tipoconta, :unidadehemopa_id, :usuario)");
            $stmt->execute(['nome' => $nome, 'senha' => $senha, 'matricula' => $matricula, 'tipoconta' => $tipo, 'unidadehemopa_id' => $local_id, 'usuario' => $usuario]);

            $_SESSION['success_message'] = 'Usuário cadastrado com sucesso!';
        }
    } else {
        // Senha incorreta
        $_SESSION['error_message'] = 'Senha do usuário atual incorreta. Tente novamente.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Usuário</title>
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container container-custom">
        <h1 class="text-center mb-4" style="color: #28a745;">Cadastrar Usuário</h1>
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
        <form method="POST" action="" onsubmit="return confirmAction(event)">
            <div class="form-group">
                <label for="nome" style="color: #28a745;">Nome:</label>
                <input type="text" name="nome" id="nome" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="matricula" style="color: #28a745;">Matrícula:</label>
                <input type="text" id="matricula" name="matricula" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="usuario" style="color: #28a745;">Usuário:</label>
                <input type="text" id="usuario" name="usuario" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="senha" style="color: #28a745;">Senha:</label>
                <input type="password" name="senha" id="senha" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="confirmar_senha">Confirmar Senha</label>
                <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required>
            </div>
            <div class="form-group">
                <label for="tipoconta" style="color: #28a745;">Função:</label>
                <select name="tipoconta" id="tipoconta" class="form-control" required>
                    <option value="normal">Normal</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="unidadehemopa_id" style="color: #28a745;">Local:</label>
                <select name="unidadehemopa_id" id="unidadehemopa_id" required>
                    <?php foreach ($locais as $local): ?>
                        <option value="<?php echo $local['id']; ?>"><?php echo $local['nome']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-custom btn-block">
                <i class="fas fa-user-plus"></i> Cadastrar
            </button>
        </form>
        <div class="text-center mt-3">
            <a href="index.php" class="btn btn-secondary">Voltar</a>
        </div>
        <a href="logout.php" class="btn btn-danger btn-lg mt-3">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
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
                        <input type="hidden" name="usuarios[]" id="hidden_usuarios">
                        <div class="form-group">
                            <label for="senha_usuario">Senha</label>
                            <input type="password" name="senha_usuario" id="senha_usuario" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Confirmar</button>
                    </form>
                </div>
            </div>
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
        function confirmAction(event) {
            event.preventDefault();
            var usuarios = [];
            document.querySelectorAll('input[name="usuarios[]"]:checked').forEach(function(checkbox) {
                usuarios.push(checkbox.value);
            });
            //if (pacotes.length === 0) {
            //    alert('Por favor, selecione pelo menos um pacote.');
             //   return false;
           // }
            document.getElementById('hidden_usuarios').value = JSON.stringify(usuarios);
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
                time = setTimeout(logout, 900000);  // Tempo em milissegundos (15 minutos)
            }
        };

        inactivityTime();
    </script>
</body>
</html>
