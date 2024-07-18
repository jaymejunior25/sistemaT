<?php
session_start();
include 'db.php'; // Inclua seu arquivo de conexão com o banco de dados

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigobarras = $_POST['codigobarras'];
    $laboratorio = $_POST['laboratorio'];

    // Consultar o status do pacote no banco de dados
    $stmt = $dbconn->prepare("SELECT status FROM pacotes WHERE codigobarras = :codigobarras ");
    $stmt->execute([':codigobarras' => $codigobarras]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $status = $result['status'];
        echo json_encode(['status' => $status]);
    } else {
        echo json_encode(['status' => 'not_found']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método não permitido']);
}
?>
