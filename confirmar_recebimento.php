<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $laboratorio = $_POST['laboratorio'];
    $data_recebimento = $_POST['data_recebimento'];
    $matricula = $_POST['matricula'];

    // Buscar o ID do laboratório com base no nome
    $stmt = $dbconn->prepare("SELECT id FROM laboratorio WHERE nome = :nome");
    $stmt->execute([':nome' => $laboratorio]);
    $laboratorio_id = $stmt->fetchColumn();

    if (!$laboratorio_id) {
        echo json_encode(['status' => 'error', 'message' => 'Laboratório não encontrado.']);
        exit();
    }

    // Verificar o laboratório vinculado ao usuário
    $stmt = $dbconn->prepare("SELECT l.nome FROM usuario_laboratorio ul 
        JOIN laboratorio l ON ul.laboratorio_id = l.id 
        JOIN usuarios u ON ul.usuario_id = u.id 
        WHERE u.matricula = :matricula");
    $stmt->execute([':matricula' => $matricula]);
    $usuario_laboratorio = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario_laboratorio || $usuario_laboratorio['nome'] != $laboratorio) {
        echo json_encode(['status' => 'error', 'message' => 'O laboratório selecionado não corresponde ao laboratório vinculado ao usuário.']);
        exit();
    }

    // Confirmar o recebimento das amostras
    $stmt = $dbconn->prepare("UPDATE pacotes SET status = 'recebidolab', usuario_recebimentoLab_id = :usuario_id, data_recebimentoLab = NOW()
                              WHERE laboratorio = :laboratorio_id AND DATE(data_recebimento) = :data_recebimento AND status = 'recebido'");
    $stmt->execute([
        ':usuario_id' => $_SESSION['user_id'],
        ':laboratorio_id' => $laboratorio_id,
        ':data_recebimento' => $data_recebimento
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Recebimento confirmado com sucesso.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Nenhuma amostra encontrada ou já recebida.']);
    }
    exit();
}
?>
