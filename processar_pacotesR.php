<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pacotes = json_decode($_POST['pacotes'], true);
    $usuario_recebimento_id = $_SESSION['user_id'];

    foreach ($pacotes as $pacote) {
        $codigobarras = $pacote['codigobarras'];
        $codigobarrasFiltrado = $pacote['codigobarrasFiltrado'];

        // Verificação e processamento do código de barras
        $digitoverificarp = substr($codigobarras, 0, 1);
        $digitoverificaru = substr($codigobarras, -1);

        
        if ($digitoverificarp === '=' && ctype_digit($digitoverificaru)) {
            $codigobarras = substr($codigobarras, 1);

        } elseif ((($digitoverificarp === 'B') || ($digitoverificarp === 'b')) && ctype_digit($digitoverificaru)) {
            $codigobarras = substr_replace($codigobarras, '0', -2, 1);

        } else {
            $codigobarras = substr($codigobarras, 1, -1);
        }

        // Verificar se o pacote está com status "enviado"
        $stmt = $dbconn->prepare("SELECT * FROM pacotes WHERE codigobarras = :codigobarras AND status = 'enviado'");
        $stmt->execute([':codigobarras' => $codigobarras]);
        $pacote_enviado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($pacote_enviado) {
            // Atualizar o status do pacote para "recebido"
            $stmt = $dbconn->prepare("UPDATE pacotes SET status = 'recebido', data_recebimento = NOW(), usuario_recebimento_id = :usuario_recebimento_id WHERE codigobarras = :codigobarras");
            $stmt->execute([
                ':usuario_recebimento_id' => $usuario_recebimento_id,
                ':codigobarras' => $codigobarras
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Pacote com código de barras ' . $codigobarras . ' não está com status "enviado".']);
            exit();
        }
    }

    echo json_encode(['status' => 'success', 'message' => 'Pacotes recebidos com sucesso!']);
}
?>
