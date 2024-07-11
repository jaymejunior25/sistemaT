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
    $laboratorioM_d = "20";

    foreach ($pacotes as $pacote) {
        $codigobarras = $pacote['codigobarras'];

        // Verificação e processamento do código de barras
        $digitoverificarp = substr($codigobarras, 0, 1);
        $digitoverificaru = substr($codigobarras, -1);

        if (($digitoverificarp === 'A' || $digitoverificarp === 'a') && ($digitoverificaru === 'B' || $digitoverificaru === 'b')) {
            $codigobarras = substr($codigobarras, 1, -1);
        } else{
            echo json_encode(['status' => 'error', 'message' => 'Código de barras inválido para LABMASTER.']);
            exit();
        }

        // Verificar se o pacote tem status "enviado"
        $stmt = $dbconn->prepare("SELECT * FROM pacotes WHERE codigobarras = :codigobarras ");
        $stmt->execute([':codigobarras' => $codigobarras]);
        $pacote = $stmt->fetch(PDO::FETCH_ASSOC);
         // Consultar o ID do laboratório correspondente ao dígito
          $stmt = $dbconn->prepare("SELECT * FROM laboratorio WHERE digito = :digito");
          $stmt->execute([':digito' => $laboratorio_id]);
          $lab = $stmt->fetch(PDO::FETCH_ASSOC);
         
          if ($lab) {
              $laboratorioL_id = $lab['id'];
              
          }
          
        if ($pacote) {
            
            $stmt = $dbconn->prepare("UPDATE pacotes SET data_recebimento = NOW(), status = 'recebido', lab_id = :laboratorio_id AS usuario_recebimento_id = :usuario_recebimento_id WHERE codigobarras = :codigobarras");
            $stmt->execute([
                ':laboratorio_id' => $laboratorioL_id,
                ':usuario_recebimento_id' => $_SESSION['user_id'],
                ':codigobarras' => $codigobarras
            ]);
        }
    }

    echo json_encode(['status' => 'success', 'message' => 'Pacotes recebidos com sucesso!']);
}
?>
