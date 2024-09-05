<?php
session_start();
include 'db.php';

$user_id = $_SESSION['user_id'];

// Remover sessão
$sql = "DELETE FROM user_sessions WHERE user_id = :user_id";
$stmt = $dbconn->prepare($sql);
$stmt->execute([':user_id' => $user_id]);

// Limpar sessão do usuário
session_destroy();
header('Location: login.php');
exit();
