<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Inicializar variáveis para filtros
$filter = $_GET['filter'] ?? '';
$local_id = $_GET['local_id'] ?? '';
$searchType = $_GET['searchType'] ?? '';
$searchQuery = $_GET['searchQuery'] ?? '';
$data_inicio = $_GET['data_inicio'] ?? '';
$dateType = $_GET['dateType'] ?? '';
$dateValue = $_GET['dateValue'] ?? '';
$colunas_selecionadas = $_GET['columns'] ?? [];

// Colunas disponíveis para seleção
$available_columns = [
    'codigobarras' => 'Codigo de Barras',
    'status' => 'Status',
    'descricao' => 'Descrição',
    'lab_nome' => 'Laboratorio',
    'data_cadastro' => 'Dt Cadastro',
    'data_envio' => 'Dt Envio',
    'envio_nome' => 'Lt Envio',
    'cadastrado_por' => 'Cadastrado por',
    'enviado_por' => 'Enviado por',
];


// Construir a consulta SQL com base nos filtros
$sql = "SELECT p.id, p.status, p.codigobarras, p.descricao, p.data_envio, p.data_cadastro, l_lab.nome AS lab_nome, l_envio.nome AS envio_nome, u_envio.usuario AS enviado_por,
        u_cadastro.usuario AS cadastrado_por, l_cadastro.nome AS cadastro_nome 
        FROM pacotes p 
        LEFT JOIN unidadehemopa l_envio ON p.unidade_envio_id = l_envio.id 
        LEFT JOIN unidadehemopa l_cadastro ON p.unidade_cadastro_id = l_cadastro.id 
        LEFT JOIN usuarios u_cadastro ON p.usuario_cadastro_id = u_cadastro.id 
        LEFT JOIN usuarios u_envio ON p.usuario_envio_id = u_envio.id 
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
        default:
            break;
    }
    $params[':dateValue'] = $dateValue;
}

if (!empty($data_inicio)) {
    $conditions[] = "p.data_cadastro >= :data_inicio";
    $params[':data_inicio'] = $data_inicio;
}

if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " AND data_recebimento IS NULL ORDER BY p.data_cadastro DESC";

$stmt = $dbconn->prepare($sql);
$stmt->execute($params);
$pacotes = $stmt->fetchAll(PDO::FETCH_ASSOC);


    include 'fpdf.php';

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

        // Método para colorir célula
        function CellColor($w, $h, $txt, $border=0, $ln=0)
        {
            $this->SetFillColor(200, 220, 255);
            $this->Cell($w, $h, $txt, $border, $ln, 'L', true);
        }

        // Método para exibir a linha da tabela
        function TableLine($data, $col_widths)
        {
            $this->SetFont('Arial', '', 8);
            foreach ($data as $i => $value) {
                $this->Cell($col_widths[$i], 6, utf8_decode($value), 1);
            }
            $this->Ln();
        }
    }

    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage('L');

    // Adicionar o cabeçalho da tabela
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 10, 'Relatório de Pendências', 0, 1, 'C');
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(0, 10, 'Total de Linhas: ' . count($pacotes), 0, 1, 'L');
    $pdf->Ln(5);
    $header = [];
    foreach ($colunas_selecionadas as $column) {
        $header[] = ucfirst(str_replace('_', ' ', $column));
    }
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->TableLine($header, array_fill(0, count($header), 30)); // Diminuir a largura das colunas

    // Adicionar os dados à tabela
    $pdf->SetFont('Arial', '', 8);
    foreach ($pacotes as $pacote) {
        $row = [];
        foreach ($colunas_selecionadas as $column) {
            if (in_array($column, ['data_cadastro', 'data_envio'])) {
                $row[] = date('d-m-Y H:i', strtotime($pacote[$column]));
            } else {
                $row[] = $pacote[$column];
            }
        }
        $pdf->TableLine($row, array_fill(0, count($header), 30)); // Diminuir a largura das colunas
    }


    $pdf->Output();
?>
