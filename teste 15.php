<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

function generateRemessaCode($dbconn) {
    do {
        $codigo = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        $stmt = $dbconn->prepare("SELECT id FROM remessas WHERE codigo = :codigo");
        $stmt->execute([':codigo' => $codigo]);
    } while ($stmt->rowCount() > 0);
    return $codigo;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $usuario_id = $_SESSION['user_id'];
    $unidade_id = $_SESSION['unidade_id'];  

    $codigo = generateRemessaCode($dbconn);
    $local = $data['local'];
    $tipos_amostras = $data['tipos_amostras'];
    $numero_tubos = $data['numero_tubos'];
    $observacao = $data['observacao'];

    $stmt = $dbconn->prepare("INSERT INTO remessas (codigo, unidade_cadastro_id, usuario_cadastro_id, tipos_amostras, numero_tubos, observacao) VALUES (:codigo, :unidade_id, :usuario_id, :tipos_amostras, :numero_tubos, :observacao)");
    $stmt->execute([
        ':codigo' => $codigo,
        ':unidade_id' => $unidade_id,
        ':usuario_id' => $usuario_id,
        ':tipos_amostras' => $tipos_amostras,
        ':numero_tubos' => $numero_tubos,
        ':observacao' => $observacao
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Cadastro realizado com sucesso!', 'codigo' => $codigo]);
}
?>
