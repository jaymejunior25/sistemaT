<?php
session_start();
include 'db.php';
include 'fpdf.php';
require 'vendor/autoload.php'; // Para incluir a biblioteca do Picqer Barcode Generator

use Picqer\Barcode\BarcodeGeneratorPNG;

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
    $user = $stmt_senha->fetch(PDO::FETCH_ASSOC);

    if (!password_verify($senha, $user['senha'])) {
        echo "Senha incorreta. Tente novamente.";
        exit;
    }

    // Gerar um número de protocolo/código de barras único
    //$protocolo = uniqid('115');
    
    // Gerar um número de protocolo/código de barras único
    $protocolo = '115' . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);

    // Inserir o  no banco de dados
    $sql = "INSERT INTO s (protocolo, cadastrado_por, unidade_cadastro_id, amostras_doador, amostras_paciente, amostras_transplante, amostras_outros, observacoes) 
            VALUES (:protocolo, :cadastrado_por, :unidade_cadastro_id, :amostras_doador, :amostras_paciente, :amostras_transplante, :amostras_outros, :observacoes)";
    $stmt = $dbconn->prepare($sql);
    $stmt->execute([
        'protocolo' => $protocolo,
        'cadastrado_por' => $cadastrado_por,
        'unidade_cadastro_id' => $unidade_cadastro_id,
        'amostras_doador' => $amostras_doador,
        'amostras_paciente' => $amostras_paciente,
        'amostras_transplante' => $amostras_transplante,
        'amostras_outros' => $amostras_outros,
        'observacoes' => $observacoes
    ]);

    // Obter o ID do  recém-inserido
    $_id = $dbconn->lastInsertId();

    // Inserir os laboratórios relacionados ao 
    $laboratorios = $_POST['laboratorios'];
    $amostras = $_POST['amostras'];



    for ($i = 0; $i < count($laboratorios); $i++) {
        $stmt = $dbconn->prepare('SELECT * FROM laboratorio WHERE digito = :digito');
        $stmt->execute([':digito' => $laboratorios[$i]]);
        $lab = $stmt->fetch(PDO::FETCH_ASSOC);


        $sql_lab = "INSERT INTO s_laboratorios (_id, laboratorio_id, numero_amostras) VALUES (:_id, :laboratorio_id, :numero_amostras)";
        $stmt_lab = $dbconn->prepare($sql_lab);
        $stmt_lab->execute([
            '_id' => $_id,
            'laboratorio_id' => $lab['id'],
            'numero_amostras' => $amostras[$i]
        ]);
    }

        // Gerar o código de barras
        $generator = new BarcodeGeneratorPNG();
        $barcode = $generator->getBarcode($protocolo, $generator::TYPE_CODE_128);
        $barcodePath = 'barcode.png';
        file_put_contents($barcodePath, $barcode);

    // Gerar o PDF com as informações do 
    class PDF extends FPDF
    {
        function Header()
        {
            $this->Image('icon2.png', 10, 6, 16); // Adicionar imagem (ajuste a posição e o tamanho conforme necessário)
            $this->SetFont('Arial', 'B', 8);
            $this->Cell(0, 5, utf8_decode('GOVERNO DO ESTADO DO PARÁ'), 0, 1, 'C');
            $this->Cell(0, 5, utf8_decode('SECRETARIA EXECUTIVA DE SAÚDE PÚBLICA'), 0, 1, 'C');
            $this->Cell(0, 5, utf8_decode('CENTRO DE HEMOTERAPIA E HEMATOLOGIA DO PARÁ'), 0, 1, 'C');
            $this->Cell(0, 5, utf8_decode('TV. PADRE EUTIQUIO, 2109 - Batista Campos TEL: (91) 3110-6500'), 0, 1, 'C');
            $this->Ln(10); // Adiciona um pequeno espaçamento após o cabeçalho
        }

        function Footer()
        {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
        }

        function SignaturePage()
        {
            $this->Ln(10);
            $this->SetFont('Arial', 'B', 10);
            $this->Cell(0, 10, 'Assinaturas', 0, 1, 'C');
            $this->Ln(10);
            $this->SetFont('Arial', '', 10);
            $this->Cell(0, 10, utf8_decode('Responsável pelo Transporte: ___________________________________________________________________'), 0, 1, 'L');
            $this->Cell(0, 10, 'Data: ______/__________/__________', 0, 1, 'L');
            $this->Ln(10);
            $this->Cell(0, 10, utf8_decode('Responsável pelo Recebimento:___________________________________________________________________'), 0, 1, 'L');
            $this->Cell(0, 10, 'Data: ______/__________/__________', 0, 1, 'L');
        }
        function Barcode($code)
        {
            $this->Image('barcode.php?code=' . urlencode($code), 10, 50, 100, 20, 'PNG');
        }

    }

    $pdf = new PDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 10, 'Protocolo: ' . $protocolo, 0, 1, 'L');
    $pdf->Cell(0, 10, 'Cadastrado por: ' . $_SESSION['username'], 0, 1, 'L');
    $pdf->Cell(0, 10, 'Unidade: ' . $_SESSION['unidade_nome'], 0, 1, 'L');
    $pdf->Cell(0, 10, 'Data de Cadastro: ' . date('d-m-Y H:i:s'), 0, 1, 'L');
    $pdf->Cell(0, 10, utf8_decode('Amostras de Doadores: ' . ($amostras_doador == '1' ? 'Sim' : 'Não')), 0, 1, 'L');
    $pdf->Cell(0, 10, utf8_decode('Amostras de Pacientes: ' . ($amostras_paciente == '1' ? 'Sim' : 'Não')), 0, 1, 'L');
    $pdf->Cell(0, 10, utf8_decode('Amostras de Transplantes: ' . ($amostras_transplante == '1' ? 'Sim' : 'Não')), 0, 1, 'L');
    $pdf->Cell(0, 10, utf8_decode('Amostras de Outros: ' . ($amostras_outros == '1' ? 'Sim' : 'Não')), 0, 1, 'L');
    $pdf->Cell(0, 10, utf8_decode('Observações: ' . $observacoes), 0, 1, 'L');
    foreach ($laboratorios as $index => $laboratorio) {
        $stmt = $dbconn->prepare('SELECT * FROM laboratorio WHERE digito = :digito');
        $stmt->execute([':digito' => $laboratorios[$index]]);
        $lab = $stmt->fetch(PDO::FETCH_ASSOC);
        $pdf->Cell(0, 10, utf8_decode('Laboratório: ') . $lab['nome'] . utf8_decode(' - Número de Amostras: ') . $amostras[$index], 0, 1, 'L');
    }

    // Adicionar o código de barras ao PDF 11521775 
    
    $pdf->Image($barcodePath, 80, 250, 50, 15, 'PNG');

    // Adicionar página de assinatura
    $pdf->SignaturePage();

    $pdf->Output();
    unlink($barcodePath); // Remove o arquivo temporário do código de barras
    exit;
}
?>
