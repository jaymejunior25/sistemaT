
<?php
session_start();
include 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];

    $stmt = $dbconn->prepare('SELECT * FROM usuarios WHERE usuario = :usuario');
    $stmt->execute([':usuario' => $usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($senha, $user['senha'])) {
        // Obter o nome da unidade para exibir 
        //$stmt = $dbconn->prepare("SELECT nome FROM unidadehemopa WHERE id = :id");
        //$stmt->execute([':id' =>$user['unidade_id']]);
        //$local = $stmt->fetch(PDO::FETCH_ASSOC);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nome'] = $user['nome'];
        $_SESSION['username'] = $user['usuario'];
        $_SESSION['user_type'] = $user['tipoconta'];
        //$_SESSION['unidade_id'] = $user['unidade_id'];
        
        //$_SESSION['unidade_nome'] = $local['nome'];
        //$_SESSION['success_message'] = 'Login com Sucesso.';
        // Inserir nova sessão
        $sql = "INSERT INTO user_sessions (user_id) VALUES (:user_id)";
        $stmt = $dbconn->prepare($sql);
        $stmt->execute([':user_id' => $user['id']]);
        header('Location: selecionar_local.php');
        exit();
    } else {
        $_SESSION['error_message'] = 'Login falhou!';
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f2f9f2; /* Fundo verde claro */
        }
        .login-container {
            max-width: 400px;
            margin: auto;
            padding: 2rem;
            border-radius: 8px;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);

        }
        .header-serda {
            max-width: 400px;
            background-color: rgb(38, 168, 147);
            color: #fff; /* Texto branco */
            text-align: center;
            margin: 0 auto;
            padding: 1rem;
            font-size: 20px;
            font-weight: bold;
            border-radius: 8px 8px 0 0;
            margin-top: 8%;
        }
        .btn-custom {
            background-color: rgb(38, 168, 147);
            color: white;
        }
        .btn-custom:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

        <div class="header-serda">SERDA <br> Sistema de Envio Recebimento e Distribuição de Amostras</div>
        <div class="login-container ">
            <h2 class="text-center mb-4" style="color: rgb(38, 168, 147);">Login</h2>
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
                    <label for="usuario" style="color: rgb(38, 168, 147);">Nome de usuário</label>
                    <input type="text" name="usuario" id="usuario" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="senha" style="color: rgb(38, 168, 147);">Senha</label>
                    <input type="password" name="senha" id="senha" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-custom btn-block">Entrar</button>
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
</body>
</html>
