<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'];

$query = $dbconn->prepare("DELETE FROM usuarios WHERE id = :id");
$query->bindParam(':id', $id);

if ($query->execute()) {
    header('Location: listar.php');
} else {
    echo "Erro ao excluir.";
}

