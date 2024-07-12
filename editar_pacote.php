<?php
session_start();
include 'db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
if ($_SESSION['user_type'] != 'admin') {
    header('Location: index.php');
    exit();
}

// Verificar se o ID do pacote foi passado pela URL
if (!isset($_GET['id'])) {
    header('Location: lista_pacotes.php');
    exit();
}

$pacote_id = $_GET['id'];

// Buscar os detalhes do pacote no banco de dados
$stmt = $dbconn->prepare("SELECT * FROM pacotes WHERE id = :id");
$stmt->execute([':id' => $pacote_id]);
$pacote = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pacote) {
    header('Location: lista_pacotes.php');
    exit();
}

// Atualizar o pacote se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigobarras = $_POST['codigobarras'];
    $descricao = $_POST['descricao'];
    $status = $_POST['status'];

    $stmt = $dbconn->prepare("UPDATE pacotes SET codigobarras = :codigobarras, descricao = :descricao, status = :status WHERE id = :id");
    $stmt->execute([
        ':codigobarras' => $codigobarras,
        ':descricao' => $descricao,
        ':status' => $status,
        ':id' => $pacote_id
    ]);

    header('Location: lista_pacotes.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Pacote</title>
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4" style="color: #28a745;">Editar Pacote</h1>
        <form method="POST" action="">
            <div class="form-group">
                <label for="codigobarras">Código de Barras</label>
                <input type="text" class="form-control" id="codigobarras" name="codigobarras" value="<?php echo htmlspecialchars($pacote['codigobarras']); ?>" required>
            </div>
            <div class="form-group">
                <label for="descricao">Descrição</label>
                <input type="text" class="form-control" id="descricao" name="descricao" value="<?php echo htmlspecialchars($pacote['descricao']); ?>" required>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="cadastrado" <?php if ($pacote['status'] == 'cadastrado') echo 'selected'; ?>>Cadastrado</option>
                    <option value="enviado" <?php if ($pacote['status'] == 'enviado') echo 'selected'; ?>>Enviado</option>
                    <option value="recebido" <?php if ($pacote['status'] == 'recebido') echo 'selected'; ?>>Recebido</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Salvar</button>
            <a href="lista_pacotes.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
