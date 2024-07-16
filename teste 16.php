<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$codigo = $_GET['codigo'];

$stmt = $dbconn->prepare("SELECT * FROM remessas WHERE codigo = :codigo");
$stmt->execute([':codigo' => $codigo]);
$remessa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$remessa) {
    echo "Remessa não encontrada.";
    exit();
}

$tipos_amostras = json_decode($remessa['tipos_amostras']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmação de Cadastro</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container container-customlistas">
        <h1 class="text-center mb-4" style="color: #28a745;">Confirmação de Cadastro</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Código da Remessa: <span id="codigoRemessa"><?php echo $remessa['codigo']; ?></span></h5>
                <p class="card-text">Tipos de Amostras: <span id="infoTiposAmostras"><?php echo implode(', ', $tipos_amostras); ?></span></p>
                <p class="card-text">Número de Tubos: <span id="infoNumeroTubos"><?php echo $remessa['numero_tubos']; ?></<span id="infoNumeroTubos"><?php echo $remessa['numero_tubos']; ?></span></p>
                <p class="card-text">Observação: <span id="infoObservacao"><?php echo $remessa['observacao']; ?></span></p>
                <p class="card-text">Observação: <span id="datacadastro"><?php echo $remessa['data_cadastro']; ?></span></p>

            </div>
        </div>
        <div class="text-center mt-4">
            <button onclick="window.print()" class="btn btn-primary">Imprimir Relatório</button>
            <a href="index.php" class="btn btn-secondary">Concluir</a>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
