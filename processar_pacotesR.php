<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pacotes = json_decode($_POST['pacotes'], true);

    foreach ($pacotes as $pacote) {
        $codigobarras = $pacote['codigobarras'];

        // Verificação e processamento do código de barras
        $digitoverificarp = substr($codigobarras, 0, 1);
        $digitoverificaru = substr($codigobarras, -1);
        if ($digitoverificarp == '=' && ctype_digit($digitoverificaru)) {
            $codigobarras = substr($codigobarras, 1);
        } elseif ($digitoverificarp == 'B' || $digitoverificarp == 'b' && ctype_digit($digitoverificaru)) {
            $codigobarras = substr_replace($codigobarras, '0', -2, 1);
        } else {
            $codigobarras = substr($codigobarras, 1, -1);
        }

        // Verificar se o pacote tem status "enviado"
        $stmt = $dbconn->prepare("SELECT status FROM pacotes WHERE codigobarras = :codigobarras");
        $stmt->execute([':codigobarras' => $codigobarras]);
        $pacote = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($pacote && $pacote['status'] === 'enviado') {
            $stmt = $dbconn->prepare("UPDATE pacotes SET data_recebimento = NOW(), status = 'recebido', usuario_recebimento_id = :usuario_recebimento_id WHERE codigobarras = :codigobarras");
            $stmt->execute([
                ':usuario_recebimento_id' => $_SESSION['user_id'],
                ':codigobarras' => $codigobarras
            ]);
        }
    }

    echo json_encode(['status' => 'success', 'message' => 'Pacotes recebidos com sucesso!']);
}
?>
