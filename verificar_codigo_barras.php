<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigobarras = $_POST['codigobarras'];

    $stmt = $dbconn->prepare("SELECT * FROM pacotes WHERE codigobarras = :codigobarras");
    $stmt->execute([':codigobarras' => $codigobarras]);
    $pacote_existente = $stmt->fetch(PDO::FETCH_ASSOC);

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
                  
              } elseif(strlen($codigobarras) === 15){
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
  
          }
              // Verificar qual dígito usar: os dois últimos ou o penúltimo
              $digito_a_utilizar = ($digitoverificarp === '=' && ctype_digit($digitoverificaru)) || (strlen($codigobarras) === 15) || (strlen($codigobarras) === 9) || (($digitoverificarp === 'A' || $digitoverificarp === 'a') && ($digitoverificaru === 'B' || $digitoverificaru === 'b')) ? $doisultimos_digitos : $penultimo_digito;
              
              // Consultar o ID do laboratório correspondente ao dígito
              $stmt = $dbconn->prepare("SELECT * FROM laboratorio WHERE digito = :digito");
              $stmt->execute([':digito' => $digito_a_utilizar]);
              $lab = $stmt->fetch(PDO::FETCH_ASSOC);
              
        if ($lab) {
            if ($pacote_existente) {
                echo json_encode(['status' => 'exists']);
            } else {
                echo json_encode(['status' => 'not_exists']);
            }
        }else{
            echo json_encode(['status' => 'lab_nexiste']);
        }
}
?>
