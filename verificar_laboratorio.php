<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigobarrasFiltrado = $_POST['codigobarrasFiltrado'];

    // Consulta ao banco de dados para encontrar o laboratório correspondente
    $stmt = $dbconn->prepare("SELECT * FROM laboratorio WHERE digito = (SELECT SUBSTR(:codigobarrasFiltrado, -1))");
    $stmt->execute([':codigobarrasFiltrado' => $codigobarrasFiltrado]);
    $lab = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($lab) {
        // Laboratório encontrado
        $laboratorio = $lab['nome'];
        $response = ['status' => 'success', 'message' => 'Laboratório válido', 'laboratorio' => $laboratorio];
    } else {
        // Laboratório não encontrado
        $response = ['status' => 'error', 'message' => 'Laboratório não encontrado para o código de barras informado'];
    }

    echo json_encode($response);
}
?>
