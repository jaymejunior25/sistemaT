<?php
include 'db.php';
include 'fpdf.php';

$dataInicio = $_GET['dataInicio'] ?? null;
$dataFim = $_GET['dataFim'] ?? null;
$local = $_GET['local'] ?? null;

$query = "SELECT codigo, descricao, data_recebimentoLab, local FROM pacotes WHERE 1=1";

if (!empty($dataInicio)) {
    $query .= " AND data_recebimentoLab >= '$dataInicio'";
}
if (!empty($dataFim)) {
    $query .= " AND data_recebimentoLab <= '$dataFim'";
}
if (!empty($local)) {
    $query .= " AND local = '$local'";
}

$result = $conn->query($query);

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);

// Cabeçalho
$pdf->Cell(40, 10, 'Código');
$pdf->Cell(80, 10, 'Descrição');
$pdf->Cell(40, 10, 'Data de Recebimento');
$pdf->Cell(40, 10, 'Local');
$pdf->Ln();

$pdf->SetFont('Arial', '', 10);

// Dados
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(40, 10, $row['codigo']);
        $pdf->Cell(80, 10, $row['descricao']);
        $pdf->Cell(40, 10, date('d-m-Y H:i', strtotime($row['data_recebimentoLab'])));
        $pdf->Cell(40, 10, $row['local']);
        $pdf->Ln();
    }
} else {
    $pdf->Cell(0, 10, 'Nenhum resultado encontrado', 1, 1, 'C');
}

$pdf->Output();
?>
