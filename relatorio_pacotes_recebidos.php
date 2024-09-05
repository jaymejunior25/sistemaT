<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

function fetchReceivedPackages($dbconn) {
    $stmt = $dbconn->prepare("
        SELECT 
            p.codigobarras, 
            p.data_recebimento, 
            u.nome AS usuario_recebimento
        FROM pacotes p
        LEFT JOIN usuarios u ON p.usuario_recebimento_id = u.id
        WHERE p.status = 'recebido'
        ORDER BY p.data_recebimento DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$receivedPackages = fetchReceivedPackages($dbconn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Pacotes Recebidos</title>
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4" style="color: #28a745;">Relatório de Pacotes Recebidos</h1>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Código de Barras</th>
                    <th>Data de Recebimento</th>
                    <th>Usuário de Recebimento</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($receivedPackages as $package): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($package['codigobarras']); ?></td>
                        <td><?php echo htmlspecialchars($package['data_recebimento']); ?></td>
                        <td><?php echo htmlspecialchars($package['usuario_recebimento']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="text-center mt-3">
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-angle-left"></i> Voltar</a>
        </div>
    </div>
    <div class="fixed-bottom toggle-footer cursor_to_down" id="footer_fixed">
        <div class="fixed-bottom border-top bg-light text-center footer-content p-2" style="z-index:4;">
            <div class="footer-text">
                Desenvolvido com &#128151; por Gerencia de Informatica - GETIN <br>
                <a class="text-reset fw-bold" href="http://www.hemopa.pa.gov.br/site/">© Todos os direitos reservados 2024 Hemopa.</a>
            </div>
        </div>
    </div>
</body>
</html>
