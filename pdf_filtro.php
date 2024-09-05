<?php
include 'fpdf.php';
include 'db.php';


// Obter os filtros e colunas selecionadas
$filter = $_GET['filter'] ?? '';
$local_id = $_GET['local_id'] ?? '';
$searchType = $_GET['searchType'] ?? '';
$searchQuery = $_GET['searchQuery'] ?? '';
$dateType = $_GET['dateType'] ?? '';
$dateValue = $_GET['dateValue'] ?? '';
$timeType = $_GET['timeType'] ?? '';
$timeStart = $_GET['timeStart'] ?? '';
$timeEnd = $_GET['timeEnd'] ?? '';
$colunas_selecionadas = $_GET['columns'] ?? [];

// Colunas disponíveis para seleção
$available_columns = [
    'codigobarras' => 'Codigo de Barras',
    'status' => 'Status',
    'descricao' => 'Descrição',
    'lab_nome' => 'Laboratorio',
    'data_cadastro' => 'Dt Cadastro',
    'data_envio' => 'Dt Envio',
    'data_recebimento' => 'Dt Recebimento',
    'envio_nome' => 'Local de Envio',
    'cadastrado_por' => 'Cadastrado por',
    'enviado_por' => 'Enviado por',
    'recebido_por' => 'Recebido por'
];

// Construir a consulta SQL com base nos filtros
$sql = 'SELECT p.id, p.status, p.codigobarras, p.descricao, p.data_envio, p.data_recebimento, p.data_cadastro, p.data_recebimentolab, l_lab.nome AS lab_nome, l_envio.nome AS envio_nome, u_envio.usuario AS enviado_por, u_recebimento.usuario AS recebido_por,
        u_cadastro.usuario AS cadastrado_por, l_cadastro.nome AS cadastro_nome, u_recebimentoLab.usuario AS recebidolab_por
        FROM pacotes p 
        LEFT JOIN unidadehemopa l_envio ON p.unidade_envio_id = l_envio.id 
        LEFT JOIN unidadehemopa l_cadastro ON p.unidade_cadastro_id = l_cadastro.id 
        LEFT JOIN usuarios u_cadastro ON p.usuario_cadastro_id = u_cadastro.id 
        LEFT JOIN usuarios u_envio ON p.usuario_envio_id = u_envio.id 
        LEFT JOIN usuarios u_recebimento ON p.usuario_recebimento_id = u_recebimento.id
        LEFT JOIN usuarios u_recebimentoLab ON p.usuario_recebimentolab_id = u_recebimentoLab.id
        LEFT JOIN laboratorio l_lab ON p.lab_id = l_lab.id';


$conditions = [];
$params = [];

if ($filter == 'enviados') {
    $conditions[] = "p.status = 'enviado'";
} elseif ($filter == 'recebidos') {
    $conditions[] = "p.status = 'recebido'";
} elseif ($filter == 'cadastrado') {
    $conditions[] = "p.status = 'cadastrado'";
}elseif ($filter == 'recebidolab') {
    $conditions[] = "p.status = 'recebidolab'";
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
            } elseif(strlen($codigobarras) === 15){
                $codigobarras = substr($codigobarras, 1);
                // Extrair os dois últimos dígitos do código de barras
                $doisultimos_digitos = substr($codigobarras, -2);
            }else{
                if ($digitoverificarp == 'B' || $digitoverificarp == 'b' && ctype_digit($digitoverificaru)) {
                    $codigobarras = substr_replace($codigobarras, '0', -2, 1);
                    // Extrair o penúltimo dígito do código de barras
                    $penultimo_digito = substr($codigobarras, -2, 1);
                } elseif(($digitoverificarp == 'A' || $digitoverificarp == 'a')&& ($digitoverificaru == 'B' || $digitoverificaru == 'b')) {
                    $codigobarras = substr($codigobarras, 1, -1);
                    $doisultimos_digitos = substr($codigobarras, -2);
                }else {
                    $codigobarras = substr($codigobarras, 1, -1);
                    // Extrair o penúltimo dígito do código de barras
                    $penultimo_digito = substr($codigobarras, -2, 1);
                }
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
        case 'usuario_recebimentoLab':
            $conditions[] = "LOWER(u_recebimentoLab.usuario) LIKE :query";
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
        case 'dataRecebimentoLab':
            $conditions[] = "DATE(p.data_recebimentolab) = :dateValue";
            break;
        default:
            break;
    }
    $params[':dateValue'] = $dateValue;
}

if (!empty($timeType) && !empty($timeStart)) {
    switch ($timeType) {
        case 'horaEnvio':
            $conditions[] = "TO_CHAR(p.data_envio, 'HH24:MI') BETWEEN :timeStart AND :timeEnd";
            break;
        case 'horaRecebimento':
            $conditions[] = "TO_CHAR(p.data_recebimento, 'HH24:MI') BETWEEN :timeStart AND :timeEnd";
            break;
        default:
            break;
    }
    $params[':timeStart'] = $timeStart;
    $params[':timeEnd'] = $timeEnd;
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
        $this->Ln(10); // Adiciona um pequeno espaçamento após o cabeçalho
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
        //$this->AddPage(); // Adicionar uma nova página para assinaturas
        $this->Ln(10);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 10, 'Assinaturas', 0, 1, 'C');
        $this->Ln(10);

        $this->SetFont('Arial', '', 10);
        $this->Cell(200, 10, utf8_decode('Responsável pela Entrega(SERDA): _________________________________________________________________'), 0, 0, 'L');
        $this->Cell(60, 10, 'Data: ____/________/________', 0, 1, 'L');
        $this->Ln(10);
        $this->Cell(190, 10, utf8_decode('Responsável pelo Recebimento:________________________________________________________________'), 0, 0, 'L');
        $this->Cell(60, 10, 'Data: ____/________/________  HORA:___:___', 0, 1, 'L');
    }

    // Método para colorir célula
    function CellColor($w, $h, $txt, $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        if ($fill) {
            $this->SetFillColor(255, 0, 0); // Cor de preenchimento (vermelho)
        }
        $this->Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
    }
        // Método para definir a largura da tabela
        function SetTableWidth($columns)
        {
            $table_width = 0;
            foreach ($columns as $column) {
                $table_width += $this->GetStringWidth($column) + 4; // Largura da célula com um pequeno espaçamento
            }
            return $table_width;
        }
            // Adicionar o total de linhas no cabeçalho da tabela
        function TableHeader($columns, $totalRows)
        {
            $this->SetFont('Arial', 'B', 8);
            $this->Cell(0, 10, 'Total de Linhas: ' . $totalRows, 0, 1, 'L');
            $this->Ln(5);
            foreach ($columns as $coluna) {
                $this->Cell(26, 8, utf8_decode($coluna), 1, 0, 'C');
            }
            $this->Ln();
        }
    
        // Método para centralizar a tabela
        function CenterTable($table_width)
        {
            $page_width = $this->GetPageWidth() - $this->rMargin - $this->lMargin;
            $x = ($page_width - $table_width) / 2 + $this->lMargin;
            $this->SetX($x);
        }
}

// Criar o PDF
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage('L');
$pdf->SetFont('Arial', 'B', 10);

// Cabeçalhos da Tabela
$pdf->Cell(0, 10, 'Listagem de Pacotes', 0, 1, 'C');
$pdf->Ln(5);

//foreach ($colunas_selecionadas as $coluna) {
  //  $pdf->Cell(26, 8, utf8_decode($available_columns[$coluna]), 1, 0, 'C');
//}
//$pdf->Ln();
$pdf->TableHeader(array_values($colunas_selecionadas), count($pacotes));

// Dados da Tabela
$pdf->SetFont('Arial', '', 6);
foreach ($pacotes as $pacote) {
    foreach ($colunas_selecionadas as $coluna) {
        $value = !empty($pacote[$coluna]) ? $pacote[$coluna] : '';
        if (in_array($coluna, ['data_cadastro', 'data_envio', 'data_recebimento', 'data_recebimentolab'])) {
            $dateTime = new DateTime($value);
            $value = $dateTime->format('d-m-Y H:i');
            //$value = !empty($value) ? date('d-m-Y H:i', strtotime($value)) : '';
        }
        $pdf->Cell(26, 7, utf8_decode($value), 1);
    }
    $pdf->Ln();
}

// Adicionar página de assinatura
$pdf->SignaturePage();

$pdf->Output();
?>
