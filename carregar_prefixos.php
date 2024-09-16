<?php
// Conexão com o banco de dados (ajuste conforme necessário)
include 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$local_id = $_SESSION['unidade_nome'];

$codigolocal_map = [
    'Castanheira' => 4,
    'Coleta Externa' => 5,
    'Metropole' => 6
];

if (!isset($codigolocal_map[$local_id])) {
    $mensagens[] = "Local não reconhecido.";
    return;
}

$codigolocal = $codigolocal_map[$local_id];

// Consulta no novo banco para buscar amostras da data atual com base no local
$sql = "SELECT cdamostra FROM coleta WHERE dtcoleta = current_date AND hrtermcoleta IS NOT NULL AND cdentjurloccoleta = :codigolocal";
$stmt = $dbconn1->prepare($sql);
$stmt->execute([':codigolocal' => $codigolocal]);

$amostras = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($amostras);
?>
