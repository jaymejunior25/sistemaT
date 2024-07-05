<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pacote_id = $_POST['pacote_id'];
    $local_envio_id = $_POST['local_envio_id'];
    $usuario_enviou_id = $_SESSION['user_id'];

    $stmt = $dbconn->prepare("UPDATE pacotes SET status = 'enviado', data_envio = NOW(), local_envio_id = :local_envio_id, usuario_envio_id = :usuario_envio_id WHERE id = :id");
    $stmt->execute(['local_envio_id' => $local_envio_id, 'usuario_envio_id' => $usuario_envio_id, 'id' => $pacote_id]);

    echo 'Pacote enviado com sucesso!';
}

// Listar pacotes que estão cadastrados mas não enviados
$stmt = $conn->prepare("
    SELECT pacotes.*, unidadehemopa.nome AS local_nome 
    FROM pacotes 
    LEFT JOIN unidadehemopa ON pacotes.local_envio_id = unidadehemopa.id
    WHERE pacotes.status = 'cadastrado'
");
$stmt->execute();
$pacotes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Listar locais para escolha
$unidadehemopa = $dbconn->query("SELECT id, nome FROM unidadehemopa")->fetchAll(PDO::FETCH_ASSOC);
?>

<form method="POST" action="">
    <label for="pacote_id">Selecione o Pacote:</label>
    <select name="pacote_id" id="pacote_id" required>
        <?php foreach ($pacotes as $pacote): ?>
            <option value="<?php echo $pacote['id']; ?>"><?php echo $pacote['descricao']; ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label for="local_envio_id">Local de Envio:</label>
    <select name="local_envio_id" id="local_envio_id" required>
        <?php foreach ($unidadehemopa as $local): ?>
            <option value="<?php echo $local['id']; ?>"><?php echo $local['nome']; ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">Enviar Pacote</button>
</form>
