<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $matricula = $_POST['matricula'];
    $usuario = $_POST['usuario'];
    $unidade = $_POST['unidade'];

    $query = $dbconn->prepare("UPDATE usuarios SET nome = :nome, matricula = :matricula, usuario = :usuario, unidade = :unidade  WHERE id = :id");
    $query->bindParam(':nome', $nome);
    $query->bindParam(':matricula', $matricula);
    $query->bindParam(':id', $id);
    $query->bindParam(':usuario', $usuario);
    $query->bindParam(':unidade', $unidade);

    if ($query->execute()) {
        header('Location: listar.php');
    } else {
        echo "Erro ao atualizar.";
    }
} else {
    $id = $_GET['id'];
    $query = $dbconn->prepare("SELECT * FROM usuarios WHERE id = :id");
    $query->bindParam(':id', $id);
    $query->execute();
    $usuario = $query->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário</title>
</head>
<body>
    <h1>Editar Usuário</h1>
    <form action="editar.php" method="post">
                <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" value="<?php echo $usuario['nome']; ?>" required><br><br>
        
        <label for="matricula">Matricula:</label>
        <input type="text" id="matricula" name="matricula" value="<?php echo $usuario['matricula']; ?>" required><br><br>

        <label for="usuario">Usuario:</label>
        <input type="text" id="usuario" name="usuario" value="<?php echo $usuario['usuario']; ?>" required><br><br>

        <label for="unidade">Usuario:</label>
        <input type="text" id="unidade" name="unidade" value="<?php echo $usuario['unidade']; ?>" required><br><br>
        
        <input type="submit" value="Atualizar">
    </form>
    <a href="listar.php">Voltar</a>
    <a href="index.php">Voltar</a>
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
