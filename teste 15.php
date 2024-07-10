
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
    $local_id = $_SESSION['unidade_id'];

    $response = [
        'status' => 'error',
        'message' => 'Falha no processamento dos pacotes.',
        'pacotes' => $pacotes,
        'processados' => [],
        'falhas' => []
    ];

    foreach ($pacotes as $pacote) {
        $laboratorio = $pacote['laboratorio'];
        $codigobarras = $pacote['codigobarrasFiltrado'];

        // Verificar se o pacote pertence ao LABMASTER
        if (substr($codigobarras, 0, 1) === 'A' && substr($codigobarras, -1) === 'B') {
            // Verificar se o pacote tem status "enviado"
            $stmt = $dbconn->prepare("SELECT status FROM pacotes WHERE codigobarras = :codigobarras");
            $stmt->execute([':codigobarras' => $codigobarras]);
            $pacote = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($pacote && $pacote['status'] === 'enviado') {
                $stmt = $dbconn->prepare("UPDATE pacotes SET data_recebimento = NOW(), status = 'recebido', usuario_recebimento_id = :usuario_recebimento_id, unidade_recebimento_id = :unidade_recebimento_id WHERE codigobarras = :codigobarras");
                $stmt->execute([
                    ':usuario_recebimento_id' => $usuario_recebimento_id,
                    ':unidade_recebimento_id' => $local_id,
                    ':codigobarras' => $codigobarras
                ]);
            }
        }
    }

    echo json_encode(['status' => 'success', 'message' => 'Pacotes recebidos com sucesso!']);
}
?>
