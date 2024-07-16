
<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data_inicio = $_POST['data_inicio'];

    $sql = "SELECT p.id, p.status, p.codigobarras, p.descricao, p.data_envio, p.data_recebimento, p.data_cadastro, l_envio.nome AS envio_nome, l_lab.nome AS lab_nome, u_envio.usuario AS enviado_por, u_recebimento.usuario AS recebido_por, u_cadastro.usuario AS cadastrado_por, l_cadastro.nome AS cadastro_nome 
            FROM pacotes p 
            LEFT JOIN unidadehemopa l_envio ON p.unidade_envio_id = l_envio.id 
            LEFT JOIN unidadehemopa l_cadastro ON p.unidade_cadastro_id = l_cadastro.id 
            LEFT JOIN usuarios u_cadastro ON p.usuario_cadastro_id = u_cadastro.id 
            LEFT JOIN usuarios u_envio ON p.usuario_envio_id = u_envio.id 
            LEFT JOIN usuarios u_recebimento ON p.usuario_recebimento_id = u_recebimento.id
            LEFT JOIN laboratorio l_lab ON p.lab_id = l_lab.id
            WHERE data_envio >= :data_inicio AND data_recebimento IS NULL";

    $stmt = $dbconn->prepare($sql);
    $stmt->execute(['data_inicio' => $data_inicio]);
    $pacotes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    include 'fpdf.php';

    class PDF extends FPDF
    {
        function Header()
        {
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(0, 10, 'Relatorio de Pacotes Nao Recebidos', 0, 1, 'C');
            $this->Ln(5);
        }

        function Footer()
        {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
        }
    }

    $pdf = new PDF();
    $pdf->AddPage($orientation = 'L');
    $pdf->SetFont('Arial', 'B', 10);

    $header = ['Codigo de Barras', 'Status', 'Laboratorio', 'Descricao', 'Data Cadastro', 'Data Envio', 'Local Envio',  'Enviado Por'];
    foreach ($header as $col) {
        $pdf->Cell(35, 7, $col, 1, 0, 'C');
    }
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 8);
    foreach ($pacotes as $row) {
        $pdf->Cell(35, 6, $row['codigobarras'], 1,0,'C');
        $pdf->Cell(35, 6, $row['status'], 1, 0 , 'C');
        $pdf->Cell(35, 6, $row['lab_nome'], 1, 0 , 'C');
        $pdf->Cell(35, 6, $row['descricao'], 1, 0 , 'C');
        if($row['data_cadastro']) {
            $pdf->Cell(35, 6, date("d-m-Y", strtotime($row['data_cadastro'])), 1, 0 ,'C');
        }
        if($row['data_envio']) {
            $pdf->Cell(35, 6, date("d-m-Y", strtotime($row['data_envio'])), 1, 0 ,'C');
        }

        $pdf->Cell(35, 6, $row['envio_nome'], 1, 0, 'C');
        $pdf->Cell(35, 6, $row['enviado_por'], 1,0 , 'C');
        $pdf->Ln();
    }

    $pdf->Output();
    exit;
}
?>
