<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data_inicio = $_POST['data_inicio'];
    $colunas_selecionadas = isset($_POST['colunas']) ? $_POST['colunas'] : [];

    // Mapear colunas para os campos no banco de dados
    $colunas = [
        'codigobarras' => 'p.codigobarras',
        'status' => 'p.status',
        'descricao' => 'p.descricao',
        'lab_nome' => 'l_lab.nome AS lab_nome',
        'data_cadastro' => 'p.data_cadastro',
        'data_envio' => 'p.data_envio',
        'cadastro_nome' => 'l_cadastro.nome AS cadastro_nome',
        'envio_nome' => 'l_envio.nome AS envio_nome',
        'cadastrado_por' => 'u_cadastro.usuario AS cadastrado_por',
        'enviado_por' => 'u_envio.usuario AS enviado_por'
    ];

    // Construir a consulta SQL com as colunas selecionadas
    $colunas_selecionadas_sql = array_intersect_key($colunas, array_flip($colunas_selecionadas));
    $sql = "SELECT " . implode(", ", $colunas_selecionadas_sql) . " 
            FROM pacotes p 
            LEFT JOIN unidadehemopa l_envio ON p.unidade_envio_id = l_envio.id 
            LEFT JOIN unidadehemopa l_cadastro ON p.unidade_cadastro_id = l_cadastro.id 
            LEFT JOIN usuarios u_cadastro ON p.usuario_cadastro_id = u_cadastro.id 
            LEFT JOIN usuarios u_envio ON p.usuario_envio_id = u_envio.id 
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
    $pdf->AddPage('L');
    $pdf->SetFont('Arial', 'B', 10);

    // Cabeçalho
    foreach ($colunas_selecionadas as $coluna) {
        $pdf->Cell(35, 7, ucwords(str_replace('_', ' ', $coluna)), 1, 0, 'C');
    }
    $pdf->Ln();

    // Dados
    $pdf->SetFont('Arial', '', 8);
    foreach ($pacotes as $row) {
        foreach ($colunas_selecionadas as $coluna) {
            if (strpos($coluna, 'data') !== false && !empty($row[$coluna])) {
                $pdf->Cell(35, 6, date("d-m-Y", strtotime($row[$coluna])), 1, 0, 'C');
            } else {
                $pdf->Cell(35, 6, $row[$coluna], 1, 0, 'C');
            }
        }
        $pdf->Ln();
    }

    $pdf->Output();
    exit;
}
?>
