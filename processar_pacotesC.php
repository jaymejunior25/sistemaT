<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pacotes = json_decode($_POST['pacotes'], true); // Recebe pacotes como JSON

    $usuario_cadastro_id = $_SESSION['user_id'];
    $local_id = $_SESSION['unidade_id'];

    foreach ($pacotes as $pacote) {
        $descricao = $pacote['descricao'];
        $codigobarras = $pacote['codigobarras'];

        // Separa o primeiro e o último dígito do código de barras
        $digitoverificarp = substr($codigobarras, 0, 1);
        $digitoverificaru = substr($codigobarras, -1);

        if ($digitoverificarp == '=' && ctype_digit($digitoverificaru)) {
            $codigobarras = substr($codigobarras, 1);
            // Extrair os dois últimos dígitos do código de barras
            $doisultimos_digitos = substr($codigobarras, -2);
        } elseif ($digitoverificarp == 'B' || $digitoverificarp == 'b' && ctype_digit($digitoverificaru)) {
            $codigobarras = substr_replace($codigobarras, '0', -2, 1);
            // Extrair o penúltimo dígito do código de barras
            $penultimo_digito = substr($codigobarras, -2, 1);
        } else {
            $codigobarras = substr($codigobarras, 1, -1);
            // Extrair o penúltimo dígito do código de barras
            $penultimo_digito = substr($codigobarras, -2, 1);
        }

        // Verificar qual digito usar: os dois últimos ou o penúltimo
        $digito_a_utilizar = ($digitoverificarp == '=' && ctype_digit($digitoverificaru)) ? $doisultimos_digitos : $penultimo_digito;

        // Consultar o ID do laboratório correspondente ao dígito
        $stmt = $dbconn->prepare("SELECT id FROM laboratorio WHERE digito = :digito");
        $stmt->execute([':digito' => $digito_a_utilizar]);
        $lab = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($lab) {
            $laboratorio_id = $lab['id'];

            // Inserir o novo pacote no banco de dados
            $stmt = $dbconn->prepare("INSERT INTO pacotes (descricao, codigobarras, usuario_cadastro_id, unidade_cadastro_id, data_cadastro, lab_id ) VALUES (:descricao, :codigobarras, :usuario_cadastro_id, :unidade_cadastro_id, NOW(), :lab_id)");
            $stmt->execute([
                ':descricao' => $descricao,
                ':codigobarras' => $codigobarras,
                ':usuario_cadastro_id' => $usuario_cadastro_id,
                ':unidade_cadastro_id' => $local_id,
                ':lab_id' => $laboratorio_id
            ]);
        }
    }

    echo json_encode(['status' => 'success', 'message' => 'Pacotes cadastrados com sucesso!']);
}
?>
