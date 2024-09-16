<?php
// Conexão com o banco de dados
include 'db.php';

$prefixo = $_POST['prefixo'] ?? '';

if ($prefixo) {
    $stmt = $dbconn->prepare("SELECT COUNT(*) as totalExistente FROM pacotes WHERE codigobarras LIKE :codigolocal");
    // $likePrefixo = $prefixo . '%';
    // $stmt->bind_param("s", $likePrefixo);
    // $stmt->execute();
    // $result = $stmt->get_result();
    // $row = $result->fetch_assoc();
    $stmt->execute([':codigolocal' => $codigolocal]);

    $amostras_existentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['totalExistente' => $row['totalExistente']]);
}
?>
