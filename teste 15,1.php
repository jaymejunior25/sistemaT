
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
        $laboratorio = $pacote['laboratorio'];

        // Verificação e processamento do código de barras
        $digitoverificarp = substr($codigobarras, 0, 1);
        $digitoverificaru = substr($codigobarras, -1);

        if ((($digitoverificarp === 'A') || ($digitoverificarp === 'a')) && (($digitoverificarp === 'B') || ($digitoverificarp === 'b'))) {
            $codigobarras = substr($codigobarras, 1, -1);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Pacote com código de barras ' . $codigobarras . ' não pertence ao LABMASTER".']);
        }

        // Verificar se o pacote está com status "enviado"
        $stmt = $dbconn->prepare("SELECT * FROM pacotes WHERE codigobarras = :codigobarras AND status = 'enviado'");
        $stmt->execute([':codigobarras' => $codigobarras]);
        $pacote_enviado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($pacote_enviado) {
            // Determinar o novo valor para o campo do laboratório
            switch ($laboratorio) {
                case 'GERAC':
                    $novoLaboratorio = '21';
                    break;
                case 'GEBIM':
                    $novoLaboratorio = '22';
                    break;
                case 'GERIM':
                    $novoLaboratorio = '23';
                    break;
                default:
                    $novoLaboratorio = null;
            }

            // Atualizar o status do pacote para "recebido" e alterar o laboratório
            if ($novoLaboratorio !== null) {
                $stmt = $dbconn->prepare("
                    UPDATE pacotes 
                    SET status = 'recebido', data_recebimento = NOW(), usuario_recebimento_id = :usuario_recebimento_id, laboratorio = :novoLaboratorio 
                    WHERE codigobarras = :codigobarras
                ");
                $stmt->execute([
                    ':usuario_recebimento_id' => $usuario_recebimento_id,
                    ':novoLaboratorio' => $novoLaboratorio,
                    ':codigobarras' => $codigobarras
                ]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Pacote com código de barras ' . $codigobarras . ' não está com status "enviado".']);
            exit();
        }
    }

    echo json_encode(['status' => 'success', 'message' => 'Pacotes recebidos com sucesso!']);
}
?>
