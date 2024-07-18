<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigobarras = $_POST['codigobarras'];
    $laboratorio = $_POST['laboratorio'];

    // Verificar se o pacote está com status "enviado"
    $stmt = $dbconn->prepare("SELECT * FROM pacotes WHERE codigobarras = :codigobarras AND status = 'enviado'");
    $stmt->execute([':codigobarras' => $codigobarras]);
    $pacote_enviado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($pacote_enviado) {
        // Separa o primeiro e o último dígito do código de barras
        $digitoverificarp = substr($codigobarras, 0, 1);
        $digitoverificaru = substr($codigobarras, -1);

        if (($digitoverificarp === 'A' || $digitoverificarp === 'a') && ($digitoverificaru === 'B' || $digitoverificaru === 'b')) {
            $doisultimos_digitos = 20; // ID do laboratório LABMASTER
            $codigobarras = substr($codigobarras, 1, -1); // Remove o primeiro e o último dígito
        } else {
            if ($digitoverificarp === '=' && ctype_digit($digitoverificaru)) {
                $codigobarras = substr($codigobarras, 1);
                $doisultimos_digitos = substr($codigobarras, -2);
            } elseif (($digitoverificarp === 'B' || $digitoverificarp === 'b') && ctype_digit($digitoverificaru)) {
                $codigobarras = substr_replace($codigobarras, '0', -2, 1);
                $penultimo_digito = substr($codigobarras, -2, 1);
            } elseif (($digitoverificarp === 'A' || $digitoverificarp === 'a') && ($digitoverificaru === 'A' || $digitoverificaru === 'A')){
                $codigobarras = substr($codigobarras, 1, -1);
                $penultimo_digito = substr($codigobarras, -2, 1);
            }
            else {
                if(strlen($codigobarras) == 9){
                    $doisultimos_digitos = 20; // ID do laboratório LABMASTER
                }else{
                    $penultimo_digito = substr($codigobarras, -2, 1);
                }
                
            }
        }
            // Verificar qual dígito usar: os dois últimos ou o penúltimo
            $digito_a_utilizar = ($digitoverificarp == '=' && ctype_digit($digitoverificaru)) || (strlen($codigobarras) === 9) || (($digitoverificarp == 'A' || $digitoverificarp == 'a') && ($digitoverificaru == 'B' || $digitoverificaru == 'b')) ? $doisultimos_digitos : $penultimo_digito;
            
        // Verificar se o nome do laboratório do código de barras corresponde ao laboratório selecionado
        $stmtLab = $dbconn->prepare("SELECT * FROM laboratorio WHERE digito = :digito");
        $stmtLab->execute([':digito' =>  $digito_a_utilizar]);
        $lab = $stmtLab->fetch(PDO::FETCH_ASSOC);

        if ($lab && $lab['nome'] === $laboratorio) {
            echo json_encode(['status' => 'enviado']);
        } else {
            echo json_encode(['status' => 'nao_compativel']);
        }
    } else {
        echo json_encode(['status' => 'nao_enviado']);
    }
}
?>