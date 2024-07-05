<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$locais = $dbconn->query("SELECT id, nome FROM unidadehemopa")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lista de Locais</title>
</head>
<body>
    <h1>Lista de Locais</h1>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Nome</th>
        </tr>
        <?php foreach ($locais as $local): ?>
        <tr>
            <td><?php echo $local['id']; ?></td>
            <td><?php echo $local['nome']; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <a href="index.php"> <i class="fas fa-angle-left"></i>Voltar</a>
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
