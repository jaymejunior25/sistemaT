<?php
session_start();
include 'db.php';
include 'fpdf.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cadastrado_por = $_SESSION['user_id'];
    $unidade_cadastro_id = $_SESSION['unidade_id'];
    $amostras_doador = isset($_POST['amostras_doador']) ? '1' : '0';
    $amostras_paciente = isset($_POST['amostras_paciente']) ? '1' : '0';
    $amostras_transplante = isset($_POST['amostras_transplante']) ? '1' : '0';
    $amostras_outros = isset($_POST['amostras_outros']) ? '1' : '0';
    $observacoes = $_POST['observacoes'];
    $senha = $_POST['senha'];

    // Verificar a senha do usuário
    $sql_senha = "SELECT senha FROM usuarios WHERE id = :user_id";
    $stmt_senha = $dbconn->prepare($sql_senha);
    $stmt_senha->execute(['user_id' => $cadastrado_por]);
    $user = $stmt_senha->fetch();

    if (!password_verify($senha, $user['senha'])) {
        echo '<script>alert("Senha incorreta!"); window.history.back();</script>';
        exit;
    }

    try {
        $dbconn->beginTransaction();

        $sql_lote = "INSERT INTO lotes (cadastrado_por, unidade_cadastro_id, amostras_doador, amostras_paciente, amostras_transplante, amostras_outros, observacoes) 
                     VALUES (:cadastrado_por, :unidade_cadastro_id, :amostras_doador, :amostras_paciente, :amostras_transplante, :amostras_outros, :observacoes)";
        $stmt_lote = $dbconn->prepare($sql_lote);
        $stmt_lote->execute([
            'cadastrado_por' => $cadastrado_por,
            'unidade_cadastro_id' => $unidade_cadastro_id,
            'amostras_doador' => $amostras_doador,
            'amostras_paciente' => $amostras_paciente,
            'amostras_transplante' => $amostras_transplante,
            'amostras_outros' => $amostras_outros,
            'observacoes' => $observacoes
        ]);

        $lote_id = $dbconn->lastInsertId();

        if (!empty($_POST['laboratorios']) && !empty($_POST['amostras'])) {
            $laboratorios = $_POST['laboratorios'];
            $amostras = $_POST['amostras'];

            $sql_laboratorios = "INSERT INTO lote_laboratorios (lote_id, laboratorio, numero_amostras) VALUES (:lote_id, :laboratorio, :numero_amostras)";
            $stmt_laboratorios = $dbconn->prepare($sql_laboratorios);

            for ($i = 0; $i < count($laboratorios); $i++) {
                $stmt_laboratorios->execute([
                    'lote_id' => $lote_id,
                    'laboratorio' => $laboratorios[$i],
                    'numero_amostras' => $amostras[$i]
                ]);
            }
        }

        $dbconn->commit();

        // Gerar o PDF
        class PDF extends FPDF {
            function Header() {
                $this->SetFont('Arial', 'B', 12);
                $this->Cell(0, 10, 'Lote Cadastrado', 0, 1, 'C');
            }

            function Footer() {
                $this->SetY(-15);
                $this->SetFont('Arial', 'I', 8);
                $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
            }
        }

        $pdf = new PDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, 'Lote ID: ' . $lote_id, 0, 1);
        $pdf->Cell(0, 10, 'Cadastrado por: ' . $_SESSION['user_nome'], 0, 1);
        $pdf->Cell(0, 10, 'Amostras de Doadores: ' . ($amostras_doador ? 'Sim' : 'Nao'), 0, 1);
        $pdf->Cell(0, 10, 'Amostras de Pacientes: ' . ($amostras_paciente ? 'Sim' : 'Nao'), 0, 1);
        $pdf->Cell(0, 10, 'Amostras de Transplantes: ' . ($amostras_transplante ? 'Sim' : 'Nao'), 0, 1);
        $pdf->Cell(0, 10, 'Amostras de Outros: ' . ($amostras_outros ? 'Sim' : 'Nao'), 0, 1);
        $pdf->Cell(0, 10, 'Observacoes: ' . $observacoes, 0, 1);

        $pdf->Ln();
        $pdf->Cell(0, 10, 'Laboratorios:', 0, 1);

        foreach ($laboratorios as $index => $laboratorio) {
            $pdf->Cell(0, 10, 'Laboratorio: ' . $laboratorio . ', Numero de Amostras: ' . $amostras[$index], 0, 1);
        }

        $pdf->Output('F', 'lote_' . $lote_id . '.pdf');

        echo '<script>alert("Lote cadastrado com sucesso!"); window.location.href = "index.php";</script>';
    } catch (Exception $e) {
        $dbconn->rollBack();
        echo '<script>alert("Erro ao cadastrar o lote. Por favor, tente novamente."); window.history.back();</script>';
    }
}
?>
