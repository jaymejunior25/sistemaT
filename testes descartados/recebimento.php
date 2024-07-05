<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pacote_id = $_POST['pacote_id'];
    $usuario_recebeu_id = $_SESSION['user_id'];

    $stmt = $dbconn->prepare("UPDATE pacotes SET status = 'recebido', data_recebimento = NOW(), usuario_recebimento_id = :usuario_recebimento_id WHERE id = :id");
    $stmt->execute(['usuario_recebimento_id' => $usuario_recebeu_id, 'id' => $pacote_id]);

    echo 'Pacote recebido com sucesso!';
}

// Listar pacotes que estão enviados mas não recebidos
$stmt = $dbconn->prepare("
    SELECT pacotes.*, unidadehemopa.nome AS local_nome 
    FROM pacotes 
    LEFT JOIN unidadehemopa ON pacotes.unidade_envio_id = unidadehemopa.id
    WHERE pacotes.status = 'enviado'
");
$stmt->execute();
$pacotes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<form method="POST" action="">
    <label for="pacote_id">Selecione o Pacote:</label>
    <select name="pacote_id" id="pacote_id" required>
        <?php foreach ($pacotes as $pacote): ?>
            <option value="<?php echo $pacote['id']; ?>"><?php echo $pacote['descricao']; ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">Receber Pacote</button>
</form>
<a href="index.php">Voltar</a>
