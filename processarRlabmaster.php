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
        $laboratorio = $pacote['laboratorio'];

        // Verificação e processamento do código de barras
        $digitoverificarp = substr($codigobarras, 0, 1);
        $digitoverificaru = substr($codigobarras, -1);



        if (($digitoverificarp == 'A' || $digitoverificarp == 'a') && ($digitoverificaru == 'B' || $digitoverificaru == 'b')) {
            $codigobarras = substr($codigobarras, 1, -1); // Remove o primeiro e o último dígito
        } elseif(strlen($codigobarras) != 9) {
            echo json_encode(['status' => 'error', 'message' => 'Pacote com código de barras ' . $codigobarras . ' não pertence ao LABMASTER".']);
            exit();
        }

        // Verificar se o pacote está com status "enviado"
        $stmt = $dbconn->prepare("SELECT * FROM pacotes WHERE codigobarras = :codigobarras AND status = 'enviado'");
        $stmt->execute([':codigobarras' => $codigobarras]);
        $pacote_enviado = $stmt->fetch(PDO::FETCH_ASSOC);



        if ($pacote_enviado) {

            // Consultar o ID do laboratório correspondente ao dígito
            $stmt = $dbconn->prepare("SELECT id FROM laboratorio WHERE digito = :digito");
            $stmt->execute([':digito' => $laboratorio]);
            $lab = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($lab) {

                $laboratorio_id = $lab['id'];

                // Atualizar o status do pacote para "recebido" e alterar o laboratório
                $stmt = $dbconn->prepare("
                    UPDATE pacotes 
                    SET status = 'recebido', data_recebimento = NOW(), usuario_recebimento_id = :usuario_recebimento_id, lab_id = :laboratorio_id 
                    WHERE codigobarras = :codigobarras
                ");
                $stmt->execute([
                    ':usuario_recebimento_id' => $usuario_recebimento_id,
                    ':laboratorio_id' => $laboratorio_id,
                    ':codigobarras' => $codigobarras
                ]);


            } else {
                echo json_encode(['status' => 'error', 'message' => 'Laboratório com dígito ' . $laboratorio . ' não encontrado.']);
                exit();
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Pacote com código de barras ' . $codigobarras . ' não está com status "enviado".']);
            exit();
        }
    }

    echo json_encode(['status' => 'success', 'message' => 'Pacotes recebidos com sucesso!']);
}
?>
