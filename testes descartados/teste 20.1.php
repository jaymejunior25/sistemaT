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

    // Criar a consulta SQL com base nas colunas selecionadas
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

    $colunas_selecionadas_sql = array_intersect_key($colunas, array_flip($colunas_selecionadas));
    $sql = "SELECT " . implode(", ", $colunas_selecionadas_sql) . " 
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
    require('fpdf/makefont/makefont.php');
    require('font/DejaVuSans.php');

    class PDF extends FPDF
    {
        function Header()
        {
            $this->SetFont('DejaVuSans', 'B', 12);
            $this->Cell(0, 10, 'Relatório de Pacotes Não Recebidos', 0, 1, 'C');
            $this->Ln(5);
        }

        function Footer()
        {
            $this->SetY(-15);
            $this->SetFont('DejaVuSans', 'I', 8);
            $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
        }
    }

    $pdf = new PDF();
    $pdf->AddPage($orientation = 'L');
    $pdf->SetFont('DejaVuSans', 'B', 10);

    // Adicionando cabeçalhos da tabela dinamicamente
    foreach ($colunas_selecionadas as $coluna) {
        $pdf->Cell(35, 7, ucwords(str_replace('_', ' ', $coluna)), 1, 0, 'C');
    }
    $pdf->Ln();

    $pdf->SetFont('DejaVuSans', '', 8);
    foreach ($pacotes as $row) {
        foreach ($colunas_selecionadas as $coluna) {
            if ($coluna == 'data_cadastro' || $coluna == 'data_envio') {
                $pdf->Cell(35, 6, $row[$coluna] ? date("d-m-Y", strtotime($row[$coluna])) : '', 1, 0, 'C');
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
