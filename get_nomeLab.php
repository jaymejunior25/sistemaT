<?php
// Incluir arquivo de conexão com o banco de dados
include 'db.php';

// Função para obter o nome do laboratório pelo ID
function getLaboratorioNameById($lab_id, $dbconn) {
    $sql = "SELECT nome FROM laboratorio WHERE id = :lab_id";
    $stmt = $dbconn->prepare($sql);
    $stmt->execute([':lab_id' => $lab_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['nome'] : '';
}

// Verificar se o ID do laboratório foi fornecido via POST
if (isset($_POST['lab_id'])) {
    $lab_id = $_POST['lab_id'];
    $nomeLaboratorio = getLaboratorioNameById($lab_id, $dbconn);
    echo json_encode(['nomeLaboratorio' => $nomeLaboratorio]);
}
?>
