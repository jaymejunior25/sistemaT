<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['id'])) {
    $lote_id = $_GET['id'];

    $sql = "SELECT * FROM lotes WHERE id = :lote_id";
    $stmt = $dbconn->prepare($sql);
    $stmt->execute(['lote_id' => $lote_id]);
    $lote = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lote_id = $_POST['lote_id'];
    $observacoes = $_POST['observacoes'];

    $sql = "UPDATE lotes SET observacoes = :observacoes WHERE id = :lote_id";
    $stmt = $dbconn->prepare($sql);
    $stmt->execute([
        'observacoes' => $observacoes,
        'lote_id' => $lote_id
    ]);

    header('Location: lista_lotes.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Lote</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1>Editar Lote</h1>
    <form method="POST" action="editar_lote.php">
        <input type="hidden" name="lote_id" value="<?php echo $lote['id']; ?>">
        <div class="form-group">
            <label for="observacoes">Observações</label>
            <textarea class="form-control" id="observacoes" name="observacoes" required><?php echo $lote['observacoes']; ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
    </form>
</div>
</body>
</html>
