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

// Excluir o pacote do banco de dados
$stmt = $dbconn->prepare("DELETE FROM pacotes WHERE id = :id");
$stmt->execute([':id' => $pacote_id]);

header('Location: lista_pacotes.php');
exit();

