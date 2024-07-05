<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];
$user_type = $_SESSION['user_type'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tela Inicial</title>
</head>
<body>
    <h1>Bem-vindo, <?php echo $username; ?>!</h1>
    <h2>Você está logado como: <?php echo ucfirst($user_type); ?></h2>
    
    <nav>
        <ul>
            <!--<li><a href="cadastro_pacote.php">Cadastrar Pacote</a></li>-->
            <li><a href="envio_pacote.php">Enviar Pacote</a></li>
            <li><a href="recebimento.php">Receber Pacote</a></li>
            <li><a href="lista_pacotes.php">Listar Pacotes</a></li>

            <?php if ($user_type === 'admin'): ?>
                <li><a href="cadastrounidade.php">Cadastrar Local</a></li>
                <li><a href="cadastro_usuarios.php">Cadastrar Usuário</a></li>
                <li><a href="listar.php">Listar Usuários</a></li>
                <li><a href="lista_locais.php">Listar Locais</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <a href="logout.php">Sair</a>
</body>
</html>
