<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $descricao = $_POST['descricao'];
    $local_envio_id = $_POST['unidade_id'];

    $stmt = $dbconn->prepare("INSERT INTO pacotes (descricao, unidade_envio_id) VALUES (:descricao, :unidade_id)");
    $stmt->execute(['descricao' => $descricao, 'unidade_id' => $local_envio_id]);

    echo 'Pacote cadastrado com sucesso!';
}

$locais = $dbconn->query("SELECT id, nome FROM locais")->fetchAll(PDO::FETCH_ASSOC);
?>

<form method="POST" action="">
    Descrição: <input type="text" name="descricao" required>
    Local de Envio: 
    <select name="local_envio_id" required>
        <?php foreach ($locais as $local): ?>
            <option value="<?php echo $local['id']; ?>"><?php echo $local['nome']; ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Cadastrar Pacote</button>
</form>

<a href="index.php">Voltar</a>

