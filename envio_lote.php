<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lote_id = $_POST['lote_id'];
    $data_envio = date('Y-m-d H:i:s');
    $enviado_por = $_SESSION['user_id'];

    $sql = "UPDATE lotes SET data_envio = :data_envio, enviado_por = :enviado_por WHERE id = :lote_id";
    $stmt = $dbconn->prepare($sql);
    $stmt->execute([
        'data_envio' => $data_envio,
        'enviado_por' => $enviado_por,
        'lote_id' => $lote_id
    ]);

    header('Location: lista_lotes.php');
    exit();
}
?>
