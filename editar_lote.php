<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['id'])) {
    $_id = $_GET['id'];

    $sql = "SELECT * FROM s WHERE id = :_id";
    $stmt = $dbconn->prepare($sql);
    $stmt->execute(['_id' => $_id]);
    $ = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_id = $_POST['_id'];
    $observacoes = $_POST['observacoes'];

    $sql = "UPDATE s SET observacoes = :observacoes WHERE id = :_id";
    $stmt = $dbconn->prepare($sql);
    $stmt->execute([
        'observacoes' => $observacoes,
        '_id' => $_id
    ]);

    header('Location: lista_s.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar </title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1>Editar </h1>
    <form method="POST" action="editar_.php">
        <input type="hidden" name="_id" value="<?php echo $['id']; ?>">
        <div class="form-group">
            <label for="observacoes">Observações</label>
            <textarea class="form-control" id="observacoes" name="observacoes" required><?php echo $['observacoes']; ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
    </form>
</div>
</body>
</html>
