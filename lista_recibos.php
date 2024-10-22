<?php
session_start();
include 'db.php';
include 'fpdf.php';
date_default_timezone_set('America/Sao_Paulo');

$sql = "UPDATE user_sessions SET last_activity = NOW() WHERE user_id = :user_id";
$stmt = $dbconn->prepare($sql);
$stmt->execute([':user_id' => $_SESSION['user_id']]);

// Função para gerar PDF
function gerarPDF($pacotesAgrupados) {
    // Pega o nome do usuário logado da sessão
    $usuarioLogado = isset($_SESSION['nome']) ? $_SESSION['nome'] : 'Usuário desconhecido';

    // Pega a hora atual de geração do recibo
    $horaAtual = date('d-m-Y H:i');
    
    $pdf = new FPDF();
    $pdf->AddPage('L');
    $pdf->Image('icon2.png', 10, 6, 16);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(0, 5, utf8_decode('GOVERNO DO ESTADO DO PARÁ'), 0, 1, 'C');
    $pdf->Cell(0, 5, utf8_decode('SECRETARIA EXECUTIVA DE SAÚDE PÚBLICA'), 0, 1, 'C');
    $pdf->Cell(0, 5, utf8_decode('CENTRO DE HEMOTERAPIA E HEMATOLOGIA DO PARÁ'), 0, 1, 'C');
    $pdf->Cell(0, 5, utf8_decode('TV. PADRE EUTIQUIO, 2109 - Batista Campos TEL: (91) 3110-6500'), 0, 1, 'C');
    $pdf->Ln(10);

    // Título do PDF
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, utf8_decode('Recibo de Envio de Amostras'), 0, 1, 'C');

    // Informações do usuário e hora
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 10, utf8_decode('Envio realizado por: ' . $usuarioLogado . ' em ' . date("d-m-Y H:i", strtotime($pacotesAgrupados[0]['data_envio']))), 0, 1, 'C');
    $pdf->Ln(5);

    // Adicionar total de amostras acima da tabela
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, utf8_decode('Total de Amostras Enviadas: ' . count($pacotesAgrupados)), 0, 1, 'C');

    // Cabeçalho da tabela
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(35, 10, 'Descricao', 1);
    $pdf->Cell(38, 10, 'Codigo de Barras', 1);
    $pdf->Cell(35, 10, 'Laboratorio', 1);
    $pdf->Cell(35, 10, 'Dt de Cadastro', 1);
    $pdf->Cell(35, 10, 'Cadastrado Por', 1);
    $pdf->Cell(38, 10, 'Local de Cadastro', 1);
    $pdf->Cell(35, 10, 'Enviou por', 1);
    $pdf->Cell(35, 10, 'Dt de Envio', 1);
    $pdf->Ln();

    // Preencher tabela com dados agrupados
    $pdf->SetFont('Arial', '', 10);

        foreach ($pacotesAgrupados as $pacote) {
            $pdf->Cell(35, 10, utf8_decode($pacote['descricao']), 1);
            $pdf->Cell(38, 10, utf8_decode($pacote['codigobarras']), 1);
            $pdf->Cell(35, 10, utf8_decode($pacote['lab_nome']), 1);
            $value = !empty($pacote['data_cadastro']) ? $pacote['data_cadastro'] : '';
            $dateTime = new DateTime($value);
            $value = $dateTime->format('d-m-Y H:i');
            $pdf->Cell(35, 10, utf8_decode($value), 1);
            $pdf->Cell(35, 10, utf8_decode($pacote['cadastrado_por']), 1);
            $pdf->Cell(38, 10, utf8_decode($pacote['cadastro_nome']), 1);
            $pdf->Cell(35, 10, utf8_decode($pacote['usuario_envio']), 1);
            $pdf->Cell(35, 10, date("d-m-Y H:i", strtotime($pacote['data_envio'])), 1);
            $pdf->Ln();
        }


    // Salvar PDF temporariamente
    $pdfOutput = 'recibo_envio_amostras.pdf';
    $pdf->Output('F', $pdfOutput);

    // Retornar caminho do arquivo PDF gerado
    return $pdfOutput;
}

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Pega o nome do usuário logado da sessão
$usuarioLogado = isset($_SESSION['nome']) ? $_SESSION['nome'] : 'Usuário desconhecido';
// Consultar pacotes
$usuarioId = $_SESSION['user_id'];
$unidadeCadastroId = $_SESSION['unidade_id'];
$localnome = $_SESSION['unidade_nome'];

$sql = "SELECT p.id, p.status, p.codigobarras, p.descricao, p.data_cadastro, u.nome AS cadastrado_por, l.nome AS lab_nome, un.nome AS cadastro_nome, 
        ue.nome AS usuario_envio, p.data_envio 
        FROM pacotes p
        LEFT JOIN usuarios u ON p.usuario_cadastro_id = u.id
        LEFT JOIN laboratorio l ON p.lab_id = l.id
        LEFT JOIN unidadehemopa un ON p.unidade_cadastro_id = un.id
        LEFT JOIN usuarios ue ON p.usuario_envio_id = ue.id
        WHERE p.unidade_cadastro_id = :unidade_cadastro_id 
        ORDER BY p.data_envio DESC";

$stmt = $dbconn->prepare($sql);
$stmt->execute([':unidade_cadastro_id' => $unidadeCadastroId]);
$pacotes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar pacotes por data de envio e descrição
$pacotesAgrupados = [];
foreach ($pacotes as $pacote) {
    $dataEnvio = date("d-m-Y H:i", strtotime($pacote['data_envio']));
    $descricao = $pacote['descricao'];
    $pacotesAgrupados[$dataEnvio][] = $pacote;
}

// Total de pacotes agrupados
$totalPacotes = count($pacotes);

// HTML para exibir a tabela
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Envios</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        .container {
            margin-top: 30px;
        }
        table {
            width: 100%;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container-custom2">

        <div class="container container-custom"  style="background-color: rgb(38, 168, 147);">
            <h1 class="text-center mb-4" style="color: #fff;">Envios Realizados Por: <?php echo ucfirst($usuarioLogado); ?></h1>
            <h2 class="text-center mb-4" style="color: #fff;">Seu usuario esta vinculado a unidade: <?php echo ucfirst($localnome); ?></h2>
        </div>
        <div class="text-center mt-3">
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-angle-left"></i> Voltar</a>
            <a href="logout.php" class="btn btn-danger "><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
        <table class="table table-bordered table-hover table-striped">
            <thead class="theadfixed">
                <tr>
                    <th>Data do Envio</th>
                    <th>Descrição</th>
                    <th>Total de Amostras</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pacotesAgrupados)): ?>
                    <?php foreach ($pacotesAgrupados as $dataEnvio => $pacotesPorData): ?>
                        <tr>
                            <td><?php echo $dataEnvio; ?></td>
                            <td><?php echo htmlspecialchars($pacotesPorData[0]['descricao']); ?></td>
                            <td><?php echo count($pacotesPorData); ?></td>
                            <td>
                                <!-- Botão para gerar PDF -->
                                <form action="" method="post">
                                    <input type="hidden" name="pacotes" value='<?php echo htmlspecialchars(json_encode($pacotesPorData)); ?>'>
                                    <input type="hidden" name="usuarioLogado" value="<?php echo htmlspecialchars($usuarioLogado); ?>">
                                    <button class="btn btn-primary" type="submit" name="gerar_pdf"><i class="far fa-file-pdf"></i>Gerar PDF</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">Nenhum envio encontrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="text-center mt-3">
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-angle-left"></i> Voltar</a>
            <a href="logout.php" class="btn btn-danger "><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="fixed-bottom toggle-footer cursor_to_down" id="footer_fixed" >
        <div class="fixed-bottom border-top bg-light text-center footer-content p-2" style="z-index:4;">
            <div class="footer-text">
                Desenvolvido com &#128151; por Gerencia de Informatica - GETIN <br>
                <a class="text-reset fw-bold" href="http://www.hemopa.pa.gov.br/site/">© Todos os direitos reservados 2024 Hemopa.</a>
            </div>
        </div>
    </div>
    <?php
    // Verificar se o botão de gerar PDF foi clicado
    if (isset($_POST['gerar_pdf'])) {
        $pacotes = json_decode($_POST['pacotes'], true);
        $usuarioLogado = $_POST['usuarioLogado'];

        // Gerar PDF
        $pdfPath = gerarPDF($pacotes);
        echo "<script>window.open('$pdfPath', '_blank');</script>";
    }
    ?>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
