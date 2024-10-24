<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$sql = "UPDATE user_sessions SET last_activity = NOW() WHERE user_id = :user_id";
$stmt = $dbconn->prepare($sql);
$stmt->execute([':user_id' => $_SESSION['user_id']]);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pacotes = json_decode($_POST['pacotes'], true);
    $usuario_recebimento_id = $_SESSION['user_id'];

    foreach ($pacotes as $pacote) {
        $codigobarras = $pacote['codigobarras'];
        $laboratorio = $pacote['laboratorio'];

        // Verificação e processamento do código de barras
        $digitoverificarp = substr($codigobarras, 0, 1);
        $digitoverificaru = substr($codigobarras, -1);

        if ($digitoverificarp === '=' && ctype_digit($digitoverificaru)) {
            $codigobarras = substr($codigobarras, 1);
            $codigobarras = substr_replace($codigobarras, 'B', 0, 1);
            $doisultimos_digitos = substr($codigobarras, -2);
            
        } elseif(strlen($codigobarras) === 15){
            $codigobarras = substr_replace($codigobarras, 'B', 0, 1);
            $doisultimos_digitos = substr($codigobarras, -2);
        }else {
            if (($digitoverificarp === 'B' || $digitoverificarp === 'b') && ctype_digit($digitoverificaru)) {
                $codigobarras = substr_replace($codigobarras, '0', -2, 1);
                $penultimo_digito = substr($codigobarras, -2, 1);
            } elseif (($digitoverificarp === 'A' || $digitoverificarp === 'a') && ($digitoverificaru === 'A' || $digitoverificaru === 'A')){
                $codigobarras = substr($codigobarras, 1, -1);
                $penultimo_digito = substr($codigobarras, -2, 1);
            }
            else {
                if(strlen($codigobarras) === 9){
                    $doisultimos_digitos = 20; // ID do laboratório LABMASTER
                }else{
                    $penultimo_digito = substr($codigobarras, -2, 1);
                }
            }
        }

        // Verificar se o nome do laboratório do código de barras corresponde ao laboratório selecionado
        $stmtLab = $dbconn->prepare("SELECT * FROM laboratorio WHERE digito = :digito");
        $stmtLab->execute([':digito' => substr($codigobarras, -2)]);
        $lab = $stmtLab->fetch(PDO::FETCH_ASSOC);

        //if ($lab && $lab['nome'] != $laboratorio) {
          //  echo json_encode(['status' => 'error', 'message' => 'O nome do laboratório do código de barras não corresponde ao laboratório selecionado.']);
            //exit();
        //}

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
