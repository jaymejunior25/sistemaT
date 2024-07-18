<?php
include 'fpdf.php';
include 'db.php';

// Recuperar os filtros e parâmetros de pesquisa
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$local_id = isset($_GET['local_id']) ? $_GET['local_id'] : '';
$searchType = isset($_GET['searchType']) ? $_GET['searchType'] : '';
$searchQuery = isset($_GET['searchQuery']) ? $_GET['searchQuery'] : '';

// Inicializar variáveis para filtros
$filter = '';
$local_id = '';
$searchType = '';
$searchQuery = '';
$dateType = '';
$dateValue = '';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['filter'])) {
        $filter = $_GET['filter'];
    }
    if (isset($_GET['local_id'])) {
        $local_id = $_GET['local_id'];
    }
    if (isset($_GET['searchType']) && isset($_GET['searchQuery'])) {
        $searchType = $_GET['searchType'];
        $searchQuery = $_GET['searchQuery'];
    }
    if (isset($_GET['dateType'])) {
        $dateType = $_GET['dateType'];
    }
    if (isset($_GET['dateValue'])) {
        $dateValue = $_GET['dateValue'];
    }
}

// Construir a consulta SQL com base nos filtros
$sql = "SELECT p.id, p.status, p.codigobarras, p.descricao, p.data_envio, p.data_recebimento, p.data_cadastro, l_lab.nome AS lab_nome, l_envio.nome AS envio_nome, u_envio.usuario AS enviado_por, u_recebimento.usuario AS recebido_por,
        u_cadastro.usuario AS cadastrado_por, l_cadastro.nome AS cadastro_nome 
        FROM pacotes p 
        LEFT JOIN unidadehemopa l_envio ON p.unidade_envio_id = l_envio.id 
        LEFT JOIN unidadehemopa l_cadastro ON p.unidade_cadastro_id = l_cadastro.id 
        LEFT JOIN usuarios u_cadastro ON p.usuario_cadastro_id = u_cadastro.id 
        LEFT JOIN usuarios u_envio ON p.usuario_envio_id = u_envio.id 
        LEFT JOIN usuarios u_recebimento ON p.usuario_recebimento_id = u_recebimento.id
        LEFT JOIN laboratorio l_lab ON p.lab_id = l_lab.id";

$conditions = [];
$params = [];

if ($filter == 'enviados') {
    $conditions[] = "p.status = 'enviado'";
} elseif ($filter == 'recebidos') {
    $conditions[] = "p.status = 'recebido'";
} elseif ($filter == 'cadastrado') {
    $conditions[] = "p.status = 'cadastrado'";
}

if (!empty($local_id)) {
    $conditions[] = "p.unidade_envio_id = :local_id";
    $params[':local_id'] = $local_id;
}

if (!empty($searchType) && !empty($searchQuery)) {
    $queryParam = '%' . strtolower($searchQuery) . '%';
    switch ($searchType) {
        case 'codigobarras':
            $codigobarras = $searchQuery;

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
            } elseif(($digitoverificarp == 'A' || $digitoverificarp == 'a')&& ($digitoverificaru == 'B' || $digitoverificaru == 'b')) {
                $codigobarras = substr($codigobarras, 1, -1);
                $doisultimos_digitos = substr($codigobarras, -2);
            } else {
                $codigobarras = substr($codigobarras, 1, -1);
                // Extrair o penúltimo dígito do código de barras
                $penultimo_digito = substr($codigobarras, -2, 1);
            }
            $queryParam = '%' . $codigobarras . '%';
            $conditions[] = "p.codigobarras LIKE :query";
            break;
        case 'usuario_cadastro':
            $conditions[] = "LOWER(u_cadastro.usuario) LIKE :query";
            break;
        case 'usuario_envio':
            $conditions[] = "LOWER(u_envio.usuario) LIKE :query";
            break;
        case 'usuario_recebimento':
            $conditions[] = "LOWER(u_recebimento.usuario) LIKE :query";
            break;
        case 'unidade_envio':
            $conditions[] = "LOWER(l_envio.nome) LIKE :query";
            break;
        case 'lab_nome':
            $conditions[] = "LOWER(l_lab.nome) LIKE :query";
            break;  
        default:
            break;
    }
    $params[':query'] = $queryParam;
}
if (!empty($dateType) && !empty($dateValue)) {
    switch ($dateType) {
        case 'dataCadastro':
            $conditions[] = "DATE(p.data_cadastro) = :dateValue";
            break;
        case 'dataEnvio':
            $conditions[] = "DATE(p.data_envio) = :dateValue";
            break;
        case 'dataRecebimento':
            $conditions[] = "DATE(p.data_recebimento) = :dateValue";
            break;
        default:
            break;
    }
    $params[':dateValue'] = $dateValue;
}

if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY p.data_cadastro DESC";  // Ordenar por data de cadastro decrescente

$stmt = $dbconn->prepare($sql);
$stmt->execute($params);
$pacotes = $stmt->fetchAll(PDO::FETCH_ASSOC);

class PDF extends FPDF
{
    // Page header
    function Header()
    {
        $this->Image('icon2.png', 10, 6, 16); // Adicionar imagem (ajuste a posição e o tamanho conforme necessário)
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(0, 5, utf8_decode('GOVERNO DO ESTADO DO PARÁ'), 0, 1, 'C');
        $this->Cell(0, 5, utf8_decode('SECRETARIA EXECUTIVA DE SAÚDE PÚBLICA'), 0, 1, 'C');
        $this->Cell(0, 5, utf8_decode('CENTRO DE HEMOTERAPIA E HEMATOLOGIA DO PARÁ'), 0, 1, 'C');
        $this->Cell(0, 5, utf8_decode('TV. PADRE EUTIQUIO, 2109 - Batista Campos TEL: (91) 3110-6500'), 0, 1, 'C');
        $this->Ln(10);
    }

    // Page footer
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    // Signature page
    function SignaturePage()
    {
       // $this->AddPage('L');
        $this->Ln(10);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 10, 'Assinaturas', 0, 1, 'C');
        $this->Ln(10);

        $this->SetFont('Arial', '', 10);
        $this->Cell(200, 10, utf8_decode('Responsável pelo Transporte: ___________________________________________________________________'), 0, 0, 'L');

        $this->Cell(60, 10, 'Data: ______/__________/__________', 0, 1, 'L');
        
        $this->Ln(10);
        
        $this->Cell(200, 10, utf8_decode('Responsável pelo Recebimento:___________________________________________________________________'), 0, 0, 'L');

        $this->Cell(60, 10, 'Data: ______/__________/__________', 0, 1, 'L');
    }
    function CellColor($w, $h, $txt, $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        if ($fill) {
            $this->SetFillColor(50, 50, 50); // Cor de preenchimento (vermelho)
        }
        $this->Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
    }

}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage('L');

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, 'Lista de Pacotes', 0, 1, 'C');
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 8);

$pdf->Cell(30, 10, 'Codigo Barras', 1, 0, 'C');
$pdf->Cell(17, 10, 'Status', 1, 0, 'C');
$pdf->Cell(30, 10, 'Descricao', 1, 0, 'C');

$pdf->Cell(28, 10, 'Local Cadastro', 1, 0, 'C');
$pdf->Cell(28, 10, 'Usuario Cadastro', 1, 0, 'C');
$pdf->Cell(23, 10, 'DT Cadastro', 1, 0, 'C');
$pdf->Cell(28, 10, 'Usuario Envio', 1, 0, 'C');
$pdf->Cell(23, 10, 'DT Envio', 1, 0, 'C');
$pdf->Cell(30, 10, 'Usuario Recebimento', 1, 0, 'C');
$pdf->Cell(23, 10, 'DT Recebimento', 1, 0, 'C');
$pdf->Cell(20, 10, 'Laboratorio', 1, 0, 'C');

$pdf->Ln();

$pdf->SetFont('Arial', '', 8);

foreach ($pacotes as $pacote) {
    $pdf->Cell(30, 10, utf8_decode($pacote['codigobarras']), 1);
    $pdf->Cell(17, 10, utf8_decode(ucfirst($pacote['status'])), 1);
    $pdf->Cell(30, 10, utf8_decode($pacote['descricao']), 1);
    
    $pdf->Cell(28, 10, utf8_decode($pacote['cadastro_nome']), 1);
    $pdf->Cell(28, 10, utf8_decode($pacote['cadastrado_por']), 1);
    $pdf->Cell(23, 10, date("d-m-Y", strtotime($pacote['data_cadastro'])), 1);
    $pdf->Cell(28, 10, utf8_decode($pacote['enviado_por']), 1);
    $pdf->Cell(23, 10, $pacote['data_envio'] ? date("d-m-Y", strtotime($pacote['data_envio'])) : '', 1);
    $pdf->Cell(30, 10, utf8_decode($pacote['recebido_por']), 1);
    $pdf->Cell(23, 10, $pacote['data_recebimento'] ? date("d-m-Y", strtotime($pacote['data_recebimento'])) : '', 1);
    $pdf->Cell(20, 10, utf8_decode($pacote['lab_nome']), 1);
    
    $pdf->Ln();
}

// Adicionar página de assinatura
$pdf->SignaturePage();

$pdf->Output();
?>
