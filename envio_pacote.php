<?php
session_start();
include 'db.php';
include 'fpdf.php';
date_default_timezone_set('America/Sao_Paulo');
function gerarPDF($pacotes, $totalPacotes) {
    // Pega o nome do usuário logado da sessão
    $usuarioLogado = isset($_SESSION['nome']) ? $_SESSION['nome'] : 'Usuário desconhecido';
    // Pega a hora atual de geração do recibo
    $horaAtual = date('d-m-Y H:i');
    
    $pdf = new FPDF();
    $pdf->AddPage('L');
    $pdf->Image('icon2.png', 10, 6, 16); // Adicionar imagem (ajuste a posição e o tamanho conforme necessário)
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(0, 5, utf8_decode('GOVERNO DO ESTADO DO PARÁ'), 0, 1, 'C');
    $pdf->Cell(0, 5, utf8_decode('SECRETARIA EXECUTIVA DE SAÚDE PÚBLICA'), 0, 1, 'C');
    $pdf->Cell(0, 5, utf8_decode('CENTRO DE HEMOTERAPIA E HEMATOLOGIA DO PARÁ'), 0, 1, 'C');
    $pdf->Cell(0, 5, utf8_decode('TV. PADRE EUTIQUIO, 2109 - Batista Campos TEL: (91) 3110-6500'), 0, 1, 'C');
    $pdf->Ln(10); // Adiciona um pequeno espaçamento após o cabeçalho


    
    // Definir título do PDF
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, utf8_decode('Recibo de Envio de Amostras'), 0, 1, 'C');

    // Adicionar o nome do usuário logado e a hora de geração do recibo
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 10, utf8_decode('Gerado por: ' . $usuarioLogado . ' em ' . $horaAtual), 0, 1, 'C');
    $pdf->Ln(5); // Adiciona um pequeno espaçamento após o nome do usuário e a hora

    // Adicionar total de amostras acima da tabela
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, utf8_decode('Total de Amostras Enviadas: ' . $totalPacotes), 0, 1, 'C');

    // Definir cabeçalho da tabela
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(35, 10, 'Descricao', 1);
    $pdf->Cell(38, 10, 'Codigo de Barras', 1);
    $pdf->Cell(35, 10, 'Laboratorio', 1);
    $pdf->Cell(35, 10, 'Dt de Cadastro', 1);
    $pdf->Cell(35, 10, 'Cadastrado Por', 1);
    $pdf->Cell(38, 10, 'Local de Cadastro', 1);
    $pdf->Cell(35, 10, 'Enviou por', 1); // Nova coluna
    $pdf->Cell(35, 10, 'Dt de Envio', 1); // Nova coluna
    $pdf->Ln();

    // Preencher tabela com dados dos pacotes
    $pdf->SetFont('Arial', '', 10);
    foreach ($pacotes as $pacote) {
        $pdf->Cell(35, 10, utf8_decode($pacote['descricao']), 1);
        $pdf->Cell(38, 10, utf8_decode($pacote['codigobarras']), 1);
        $pdf->Cell(35, 10, utf8_decode($pacote['lab_nome']), 1);
        //$pdf->Cell(35, 10, date("d-m-Y H:i", strtotime($pacote['data_cadastro'])), 1);
        $value = !empty($pacote['data_cadastro']) ? $pacote['data_cadastro'] : '';
        $dateTime = new DateTime($value);
        $value = $dateTime->format('d-m-Y H:i');
        $pdf->Cell(35, 10, utf8_decode($value), 1);
        $pdf->Cell(35, 10, utf8_decode($pacote['cadastrado_por']), 1);
        $pdf->Cell(38, 10, utf8_decode($pacote['cadastro_nome']), 1);
        $pdf->Cell(35, 10, utf8_decode($pacote['usuario_envio']), 1); // Novo campo
        $pdf->Cell(35, 10, date("d-m-Y H:i", strtotime($pacote['data_envio'])), 1); // Novo campo
        $pdf->Ln();
    }

    // Salvar PDF temporariamente
    $pdfOutput = 'recibo_envio_amostras.pdf';
    $pdf->Output('F', $pdfOutput);

    // Retornar caminho do arquivo PDF gerado
    return $pdfOutput;
}

// Verificar sessão do usuário
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$sql = "UPDATE user_sessions SET last_activity = NOW() WHERE user_id = :user_id";
$stmt = $dbconn->prepare($sql);
$stmt->execute([':user_id' => $_SESSION['user_id']]);

$local_envio_id = $_SESSION['unidade_id'];
$status_cadastro = 'cadastrado';
$pacotes = [];

// Buscar a última descrição enviada
$sql = "SELECT descricao FROM pacotes 
        WHERE unidade_cadastro_id = :unidade_cadastro_id and DATE(data_envio) = DATE(NOW())
        AND status = 'enviado' 
        ORDER BY data_envio DESC LIMIT 1";

$stmt = $dbconn->prepare($sql);
$stmt->execute([':unidade_cadastro_id' => $local_envio_id]);
$ultimaDescricao = $stmt->fetchColumn();

// Definir a descrição padrão com base na última descrição
if ($ultimaDescricao) {
    // Exemplo: Se a última descrição é '2° ENVIO', devemos definir a descrição para '3° ENVIO'
    preg_match('/(\d+)(° ENVIO)/', $ultimaDescricao, $matches);
    if ($matches) {
        $numeroEnvio = (int)$matches[1] + 1; // Incrementa o número
        $descricaoPadrao = $numeroEnvio . '° ENVIO'; // Define a nova descrição
    } else {
        $descricaoPadrao = "1° ENVIO"; // Caso a descrição não siga o padrão esperado
    }
} else {
    $descricaoPadrao = "1° ENVIO"; // Caso não haja amostras enviadas
}


// Filtragem e busca de pacotes
$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : date('Y-m-d');
$filter_description = isset($_GET['filter_description']) ? $_GET['filter_description'] : null;

// Construir a consulta SQL com os filtros
$sql = "SELECT p.id, p.status, p.codigobarras, p.descricao, p.data_cadastro, l_lab.nome AS lab_nome,
        u_cadastro.usuario AS cadastrado_por, l_cadastro.nome AS cadastro_nome,
        u_envio.usuario AS usuario_envio, p.data_envio 
        FROM pacotes p 
        LEFT JOIN usuarios u_cadastro ON p.usuario_cadastro_id = u_cadastro.id 
        LEFT JOIN unidadehemopa l_cadastro ON p.unidade_cadastro_id = l_cadastro.id 
        LEFT JOIN laboratorio l_lab ON p.lab_id = l_lab.id
        LEFT JOIN usuarios u_envio ON p.usuario_envio_id = u_envio.id
        WHERE unidade_cadastro_id = :unidade_cadastro_id AND status = :status_cadastro";

// Adicionar os filtros à consulta SQL
$params = [
    ':unidade_cadastro_id' => $local_envio_id,
    ':status_cadastro' => $status_cadastro,
];

if ($filter_date) {
    $sql .= " AND DATE(p.data_cadastro) = :filter_date";
    $params[':filter_date'] = $filter_date;
}

if ($filter_description) {
    $sql .= " AND p.descricao LIKE :filter_description";
    $params[':filter_description'] = '%' . $filter_description . '%';
}

$sql .= " ORDER BY p.data_cadastro DESC";

$stmt = $dbconn->prepare($sql);
$stmt->execute($params);
$pacotes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($pacotes) {
    // Pegue a descrição do primeiro pacote
    $descricao_primeiro_pacote = $pacotes[0]['descricao'];
} else {
    // Caso não haja pacotes, definir a descrição como null
    $descricao_primeiro_pacote = null;
}

// Agrupa os pacotes pelos 13 primeiros dígitos do código de barras
$pacoteAgrupado = [];

foreach ($pacotes as $pacote) {
    // $prefixoCodigoBarras = substr($pacote['codigobarras'], 0, 13); // Pega os 13 primeiros dígitos
    if (strlen($pacote['codigobarras']) === 15) {
        $prefixoCodigoBarras = substr($pacote['codigobarras'], 0, 13);
    } elseif (strlen($pacote['codigobarras']) === 12) {
        $prefixoCodigoBarras = substr($pacote['codigobarras'], 0, 10); 
    } elseif (strlen($pacote['codigobarras']) === 17) {
        $prefixoCodigoBarras = substr($pacote['codigobarras'], 5, 10);
    } else {
        $prefixoCodigoBarras = $pacote['codigobarras']; 
    }
    if (!isset($pacoteAgrupado[$prefixoCodigoBarras])) {
        $pacoteAgrupado[$prefixoCodigoBarras] = [
            'prefixo' => $prefixoCodigoBarras,
            'count' => 0,
            'pacotes' => []
        ];
    }
    $pacoteAgrupado[$prefixoCodigoBarras]['pacotes'][] = $pacote;
    $pacoteAgrupado[$prefixoCodigoBarras]['count']++;
}

// Calcular o total de pacotes
$totalPacotes = count($pacotes);

// Contar o número de amostras por laboratório
$amostrasPorLab = [];

foreach ($pacotes as $pacote) {
    $lab_nome = $pacote['lab_nome'];
    if (!isset($amostrasPorLab[$lab_nome])) {
        $amostrasPorLab[$lab_nome] = 0;
    }
    $amostrasPorLab[$lab_nome]++;
}



// Processar o envio dos pacotes
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_password']) && isset($_POST['pacotes'])) {
    $pacotes_selecionados = $_POST['pacotes'];
    $stmt = $dbconn->prepare('SELECT senha FROM usuarios WHERE id = :id');
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (password_verify($_POST['confirm_password'], $user['senha'])) {
        // Atualiza apenas os pacotes que foram filtrados e mostrados na tela
        $sql = "UPDATE pacotes SET status = 'enviado', data_envio = NOW(), unidade_envio_id = :unidade_envio_id, usuario_envio_id = :usuario_envio_id
                WHERE unidade_cadastro_id = :unidade_cadastro_id AND status = :status_cadastro";

        // Adicionar filtros ao update, se existirem
        $params = [
            ':unidade_envio_id' => $_SESSION['unidade_id'],
            ':usuario_envio_id' => $_SESSION['user_id'],
            ':unidade_cadastro_id' => $local_envio_id,
            ':status_cadastro' => $status_cadastro,
        ];

        if ($filter_date) {
            $sql .= " AND DATE(data_cadastro) = :filter_date";
            $params[':filter_date'] = $filter_date;
        }

        if ($filter_description) {
            $sql .= " AND descricao LIKE :filter_description";
            $params[':filter_description'] = '%' . $filter_description . '%';
        }

        $stmt = $dbconn->prepare($sql);
        $stmt->execute($params);

        $_SESSION['success_message'] = 'Pacotes enviados com sucesso.';

        // **Novo SELECT após o UPDATE**
        $sql = "SELECT p.id, p.status, p.codigobarras, p.descricao, p.data_cadastro, l_lab.nome AS lab_nome,
                u_cadastro.usuario AS cadastrado_por, l_cadastro.nome AS cadastro_nome,
                u_envio.usuario AS usuario_envio, p.data_envio 
                FROM pacotes p 
                LEFT JOIN usuarios u_cadastro ON p.usuario_cadastro_id = u_cadastro.id 
                LEFT JOIN unidadehemopa l_cadastro ON p.unidade_cadastro_id = l_cadastro.id 
                LEFT JOIN laboratorio l_lab ON p.lab_id = l_lab.id
                LEFT JOIN usuarios u_envio ON p.usuario_envio_id = u_envio.id
                WHERE unidade_cadastro_id = :unidade_cadastro_id AND status = 'enviado' ";

        // Adicionar os filtros à consulta SQL para garantir que estamos obtendo os pacotes corretos
        $params = [
            ':unidade_cadastro_id' => $local_envio_id,
        ];

        if ($filter_date) {
            $sql .= " AND DATE(p.data_cadastro) = :filter_date";
            $params[':filter_date'] = $filter_date;
        }

        if ($descricao_primeiro_pacote !== null) {
            $sql .= " AND p.descricao = :descricao_primeiro_pacote";
            $params[':descricao_primeiro_pacote'] = $descricao_primeiro_pacote;
        }

        $sql .= " ORDER BY p.data_cadastro DESC";

        $stmt = $dbconn->prepare($sql);
        $stmt->execute($params);
        $pacotes1 = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalPacotes = count($pacotes1);

        $pdfPath = gerarPDF($pacotes1, $totalPacotes);
        echo "<script>window.open('$pdfPath', '_blank');</script>";
        // header('Location: index.php');
    } else {
        $_SESSION['error_message'] = 'Senha incorreta. Por favor, tente novamente.';
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Pacote</title>
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <div class="container container-customlistas">
        <h1 class="text-center mb-4">Enviar Amostras</h1>
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        <form method="GET" action="">
            <div class="form-row">
            <div class="col-md-4 mb-3">
                <label for="filter_date">Filtrar por Data de Cadastro:</label>
                <input type="date" class="form-control" id="filter_date" name="filter_date" 
                    value="<?php echo isset($_GET['filter_date']) ? htmlspecialchars($_GET['filter_date']) : date('Y-m-d'); ?>">
            </div>
                <div class="col-md-4 mb-3">
                    <label for="filter_description">Filtrar por Descrição:</label>
                    <div class="form-group">
                    <!-- <label for="descricao" style="color: #28a745;">Descrição:</label> -->
                    <select name="descricao" id="descricao" class="form-control" required>
                        <option value="1° ENVIO" <?php echo ($descricaoPadrao == "1° ENVIO") ? 'selected' : ''; ?>>1° ENVIO</option>
                        <option value="2° ENVIO" <?php echo ($descricaoPadrao == "2° ENVIO") ? 'selected' : ''; ?>>2° ENVIO</option>
                        <option value="3° ENVIO" <?php echo ($descricaoPadrao == "3° ENVIO") ? 'selected' : ''; ?>>3° ENVIO</option>
                        <option value="4° ENVIO" <?php echo ($descricaoPadrao == "4° ENVIO") ? 'selected' : ''; ?>>4° ENVIO</option>
                    </select>
                </div>
                    <!-- <button type="submit" class="btn btn-primary">Filtrar</button> -->
                </div>
                <div class="col-md-4 mb-3 align-self-center">
                    <br>
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </div> 
            </div>
        </form>
                
        <!-- Exibir total de amostras por laboratório -->
        <h4>Total por Laboratório:</h4>
        <ul>
            <?php foreach ($amostrasPorLab as $lab_nome => $count): ?>
                <li><?php echo $lab_nome . ': ' . $count . ' amostras'; ?></li>
            <?php endforeach; ?>
        </ul>
        <div class="mb-3 text-center">
            <h4>Total de Amostras: <?php echo $totalPacotes; ?></h4> <!-- Total de Pacotes -->
        </div>
        
        <form method="POST" action="" onsubmit="return confirmAction(event)">
            <div class="form-group">
                <label for="pacotes">Pacotes Cadastrados:</label>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <?php foreach ($pacoteAgrupado as $grupo): ?>
                            <tr>
                                <td colspan="6" style="background-color: rgb(38, 168, 147); color: aliceblue;">
                                    Prefixo: <?php echo htmlspecialchars($grupo['prefixo']); ?> (Total: <?php echo $grupo['count']; ?> Amostras)
                                </td>
                            </tr>
                        
                            <?php foreach ($grupo['pacotes'] as $pacote): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($pacote['descricao']); ?></td>
                                    <td><?php echo htmlspecialchars($pacote['codigobarras']); ?></td>
                                    <td><?php echo htmlspecialchars($pacote['lab_nome']); ?></td>
                                    <td><?php echo htmlspecialchars(date("d-m-Y", strtotime($pacote['data_cadastro']))); ?></td>
                                    <td><?php echo htmlspecialchars($pacote['cadastrado_por']); ?></td>
                                    <td><?php echo htmlspecialchars($pacote['cadastro_nome']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <button type="submit" class="btn btn-custom btn-block"><i class="fas fa-paper-plane"></i> Enviar</button>
        </form>
        <div class="text-center mt-3">
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-angle-left"></i> Voltar</a>
        </div>
        <a href="logout.php" class="btn btn-danger btn-lg mt-3"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    <!-- Modal de Confirmação -->
    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirme sua Senha</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="confirmForm" method="post" action="">
                        <input type="hidden" name="pacotes[]" id="hidden_pacotes">
                        <div class="form-group">
                            <label for="confirm_password">Senha</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Confirmar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="fixed-bottom toggle-footer cursor_to_down" id="footer_fixed">
        <div class="fixed-bottom border-top bg-light text-center footer-content p-2" style="z-index:4;">
            <div class="footer-text">
                Desenvolvido com &#128151; por Gerencia de Informatica - GETIN <br>
                <a class="text-reset fw-bold" href="http://www.hemopa.pa.gov.br/site/">© Todos os direitos reservados 2024 Hemopa.</a>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function confirmAction(event) {
            event.preventDefault();
            var pacotes = [];
            document.querySelectorAll('input[name="pacotes[]"]:checked').forEach(function(checkbox) {
                pacotes.push(checkbox.value);
            });
            //if (pacotes.length === 0) {
            //    alert('Por favor, selecione pelo menos um pacote.');
            //    return false;
            //}
            document.getElementById('hidden_pacotes').value = JSON.stringify(pacotes);
            $('#confirmModal').modal('show');
        }

        // Função para monitorar inatividade
        let inactivityTime = function () {
            let time;
            window.onload = resetTimer;
            document.onmousemove = resetTimer;
            document.onkeypress = resetTimer;
            document.onscroll = resetTimer;
            document.onclick = resetTimer;

            function logout() {
                alert("Você foi desconectado devido à inatividade.");
                window.location.href = 'logout.php';
            }

            function resetTimer() {
                clearTimeout(time);
                time = setTimeout(logout, 900000);  // Tempo em milissegundos 900000 = (15 minutos)
            }
        };

        inactivityTime();
    </script>
</body>
</html>
