<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['id'])) {
    $lote_id = $_GET['id'];

    $sql = "DELETE FROM lotes WHERE id = :lote_id";
    $stmt = $dbconn->prepare($sql);
    $stmt->execute(['lote_id' => $lote_id]);

    header('Location: lista_lotes.php');
    exit;
}
?>
