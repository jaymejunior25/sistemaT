<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pacotes = json_decode($_POST['pacotes'], true);

    $usuario_cadastro_id = $_SESSION['user_id'];
    $local_id = $_SESSION['unidade_id'];

    $ids_laboratorios = [];
    $messages = [];

    foreach ($pacotes as $pacote) {
        $descricao = $pacote['descricao'];
        $codigobarras = $pacote['codigobarras'];

        // Verificar se o código de barras já existe no banco de dados
        $stmt = $dbconn->prepare("SELECT * FROM pacotes WHERE codigobarras = :codigobarras");
        $stmt->execute([':codigobarras' => $codigobarras]);
        $pacote_existente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($pacote_existente) {
            $messages[] = 'Pacote com código de barras ' . $codigobarras . ' já existe no banco de dados.';
            continue;
        }

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
                if(strlen($codigobarras) === 9){
                    $doisultimos_digitos = 20; // ID do laboratório LABMASTER
                }else{
                    $penultimo_digito = substr($codigobarras, -2, 1);
                }
                
            }

        }
            // Verificar qual dígito usar: os dois últimos ou o penúltimo
            $digito_a_utilizar = ($digitoverificarp === '=' && ctype_digit($digitoverificaru)) || (strlen($codigobarras) === 9) || (($digitoverificarp === 'A' || $digitoverificarp === 'a') && ($digitoverificaru === 'B' || $digitoverificaru === 'b')) ? $doisultimos_digitos : $penultimo_digito;
            
            // Consultar o ID do laboratório correspondente ao dígito
            $stmt = $dbconn->prepare("SELECT * FROM laboratorio WHERE digito = :digito");
            $stmt->execute([':digito' => $digito_a_utilizar]);
            $lab = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($lab) {
                $laboratorio_id = $lab['id'];
                $ids_laboratorios[] = $laboratorio_id;
            }

            // Inserir o novo pacote no banco de dados
        $stmt = $dbconn->prepare("INSERT INTO pacotes (descricao, codigobarras, usuario_cadastro_id, unidade_cadastro_id, data_cadastro, lab_id) VALUES (:descricao, :codigobarras, :usuario_cadastro_id, :unidade_cadastro_id, NOW(), :lab_id)");
        $stmt->execute([
            ':descricao' => $descricao,
            ':codigobarras' => $codigobarras,
            ':usuario_cadastro_id' => $usuario_cadastro_id,
            ':unidade_cadastro_id' => $local_id,
            ':lab_id' => $laboratorio_id
        ]);


    }

    if (empty($messages)) {
        echo json_encode(['status' => 'success', 'message' => 'Pacotes cadastrados com sucesso!', 'labs' => $ids_laboratorios]);
    } else {
        echo json_encode(['status' => 'error', 'message' => $messages]);
    }
}
?>
