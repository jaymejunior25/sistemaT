<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigobarras = $_POST['codigobarras'];

    $stmt = $dbconn->prepare("SELECT * FROM pacotes WHERE codigobarras = :codigobarras");
    $stmt->execute([':codigobarras' => $codigobarras]);
    $pacote_existente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($pacote_existente) {
        echo json_encode(['status' => 'exists']);
    } else {
        echo json_encode(['status' => 'not_exists']);
    }
}
?>
