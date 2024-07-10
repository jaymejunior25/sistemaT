<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pacotes = json_decode($_POST['pacotes'], true);
    $laboratorio_id = $_POST['laboratorio_id'];

    foreach ($pacotes as $pacote) {
        $codigobarras = $pacote['codigobarras'];

        // Verificação e processamento do código de barras
        $digitoverificarp = substr($codigobarras, 0, 1);
        $digitoverificaru = substr($codigobarras, -1);

        if (($digitoverificarp == 'A' || $digitoverificarp == 'a') && ($digitoverificaru == 'B' || $digitoverificaru == 'b')) {
            $codigobarras = substr($codigobarras, 1, -1);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Código de barras inválido para LABMASTER.']);
            exit();
        }

        // Verificar se o pacote tem status "enviado"
        $stmt = $dbconn->prepare("SELECT status FROM pacotes WHERE codigobarras = :codigobarras AND lab_id = :laboratorio_id");
        $stmt->execute([':codigobarras' => $codigobarras, ':laboratorio_id' => $laboratorio_id]);
        $pacote = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($pacote && $pacote['status'] === 'enviado') {
            $stmt = $dbconn->prepare("UPDATE pacotes SET data_recebimento = NOW(), status = 'recebido', usuario_recebimento_id = :usuario_recebimento_id WHERE codigobarras = :codigobarras AND lab_id = :laboratorio_id");
            $stmt->execute([
                ':usuario_recebimento_id' => $_SESSION['user_id'],
                ':codigobarras' => $codigobarras,
                ':laboratorio_id' => $laboratorio_id
            ]);
        }
    }

    echo json_encode(['status' => 'success', 'message' => 'Pacotes recebidos com sucesso!']);
}
?>
