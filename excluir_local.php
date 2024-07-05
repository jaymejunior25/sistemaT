<?php
session_start();
include 'db.php';

// Verificar se o usuário está logado e é admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit;
}
if ($_SESSION['user_type'] != 'admin') {
    header('Location: index.php');
    exit();
}
// Verificar se o ID do local a ser excluído foi passado via GET
if (!isset($_GET['id'])) {
    header('Location: lista_pacotes.php');
    exit;
}

$local_id = $_GET['id'];

// Excluir local do banco de dados
$stmt = $dbconn->prepare('DELETE FROM unidadehemopa WHERE id = :id');
$stmt->execute(['id' => $local_id]);

$_SESSION['success_message'] = 'Local excluído com sucesso.';
header('Location: lista_pacotes.php');
exit;

