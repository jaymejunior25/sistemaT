<?php
session_start();
include 'db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
if ($_SESSION['unidade_id'] != '1' && $_SESSION['user_type'] != 'admin') {
    header('Location: index.php');
    exit();
}

$stmt = $dbconn->prepare("SELECT id, nome FROM unidadehemopa");
$stmt->execute();
$locais = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Inicializar variáveis para filtros
$filter = '';
$local_id = '';
$searchType = '';
$searchQuery = '';
$dateType = '';
$dateValue = '';
$timeType = '';
$timeStart = '';
$timeEnd = '';
$colunas_selecionadas = [];

// Colunas disponíveis para seleção
$available_columns = [
    'codigobarras' => 'Codigo de Barras',
    'status' => 'Status',
    'descricao' => 'Descrição',
    'lab_nome' => 'Laboratorio',
    'data_cadastro' => 'Data de Cadastro',
    'data_envio' => 'Data de Envio',
    'data_recebimento' => 'Dt de Recebimento',
    'data_recebimentolab' => 'Dt de Receb. LAB',
    'envio_nome' => 'Local de Envio',
    'cadastrado_por' => 'Cadastrado por',
    'enviado_por' => 'Enviado por',
    'recebido_por' => 'Recebido por',
    'recebidolab_por' => 'Recebido LAB por'
    
];

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
    if (isset($_GET['timeType'])) {
        $timeType = $_GET['timeType'];
    }
    if (isset($_GET['timeStart'])) {
        $timeStart = $_GET['timeStart'];
    }
    if (isset($_GET['timeEnd'])) {
        $timeEnd = $_GET['timeEnd'];
    }
    if (isset($_GET['columns'])) {
        $colunas_selecionadas = $_GET['columns'];
    } else {
        $colunas_selecionadas = array_diff(array_keys($available_columns), ['data_cadastro', 'cadastrado_por']);// as colunas selecionadas por padrão

        //$colunas_selecionadas = array_keys($available_columns); // Todas as colunas selecionadas por padrão
    }
}

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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar Pacotes</title>
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css">
    <link href="styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            padding: 0;
        }
        
        .main-content {
            padding: 20px;
            height: calc(100vh - 40px); /* Ajusta a altura para ocupar o restante da tela */
            overflow-y: auto;
        }

        .table-responsive {
            height: 100%; /* Ocupa toda a altura disponível */
            overflow-y: auto;
        }
        
        table {
            background-color: #ffffff; /* Cor de fundo da tabela */
        }

        th, td {
            vertical-align: middle; /* Alinha o conteúdo verticalmente */
        }
        .fixed-bottom {
            position: fixed;
            bottom: 0;
            width: 100%;
        }

    </style>
</head>
<body>
    <div class="container container-custom2">
        <h2>Listagem de Amostras</h2>
        <p><strong>Total de Amostras:</strong> <?php echo count($pacotes); ?></p>

        <!-- Filtros e Botões -->
        <div class="row justify-content-between mb-3">
            <form method="get" action="">
                <div class="form-row">
                    <div class="col-md-2">
                        <label for="filter">Filtrar por Status:</label>
                        <select name="filter" id="filter" class="form-control">
                            <option value="">Todos</option>
                            <option value="cadastrado" <?php if ($filter == 'cadastrado') echo 'selected'; ?>>Cadastrado</option>
                            <option value="enviados" <?php if ($filter == 'enviados') echo 'selected'; ?>>Enviado</option>
                            <option value="recebidos" <?php if ($filter == 'recebidos') echo 'selected'; ?>>Recebido</option>
                            <option value="recebidolab" <?php if ($filter == 'recebidolab') echo 'selected'; ?>>Recebido LAB</option>

                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="local_id">Filtrar por Unidade:</label>
                        <select name="local_id" id="local_id" class="form-control">
                            <option value="">Todos</option>
                            <?php foreach ($locais as $local): ?>
                                <option value="<?php echo $local['id']; ?>" <?php if ($local_id == $local['id']) echo 'selected'; ?>><?php echo $local['nome']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="searchType">Tipo de Pesquisa:</label>
                        <select name="searchType" id="searchType" class="form-control">
                            <option value="">Nenhum</option>
                            <option value="codigobarras" <?php if ($searchType == 'codigobarras') echo 'selected'; ?>>Código de Barras</option>
                            <option value="usuario_cadastro" <?php if ($searchType == 'usuario_cadastro') echo 'selected'; ?>>Cadastrado por</option>
                            <option value="usuario_envio" <?php if ($searchType == 'usuario_envio') echo 'selected'; ?>>Enviado por</option>
                            <option value="usuario_recebimento" <?php if ($searchType == 'usuario_recebimento') echo 'selected'; ?>>Recebido por</option>
                            <option value="usuario_recebimentoLab" <?php if ($searchType == 'usuario_recebimentoLab') echo 'selected'; ?>>Recebido LAB por</option>
                            <option value="unidade_envio" <?php if ($searchType == 'unidade_envio') echo 'selected'; ?>>Unidade de Envio</option>
                            <option value="lab_nome" <?php if ($searchType == 'lab_nome') echo 'selected'; ?>>Laboratório</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="searchQuery">Pesquisar:</label>
                        <input type="text" name="searchQuery" id="searchQuery" value="<?php echo htmlspecialchars($searchQuery); ?>" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label for="dateType">Tipo de Data:</label>
                        <select name="dateType" id="dateType" class="form-control">
                            <option value="">Nenhum</option>
                            <option value="dataCadastro" <?php if ($dateType == 'dataCadastro') echo 'selected'; ?>>Data de Cadastro</option>
                            <option value="dataEnvio" <?php if ($dateType == 'dataEnvio') echo 'selected'; ?>>Data de Envio</option>
                            <option value="dataRecebimento" <?php if ($dateType == 'dataRecebimento') echo 'selected'; ?>>Data de Recebimento</option>
                            <option value="dataRecebimentoLab" <?php if ($dateType == 'dataRecebimentoLab') echo 'selected'; ?>>Data de Recebimento LAB</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="dateValue">Data:</label>
                        <input type="date" name="dateValue" id="dateValue" value="<?php echo htmlspecialchars($dateValue); ?>" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label for="timeType">Filtrar por Hora:</label>
                        <select name="timeType" id="timeType" class="form-control">
                            <option value="">Nenhum</option>
                            <option value="horaEnvio" <?php if ($timeType == 'horaEnvio') echo 'selected'; ?>>Hora de Envio</option>
                            <option value="horaRecebimento" <?php if ($timeType == 'horaRecebimento') echo 'selected'; ?>>Hora de Recebimento</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="timeStart">Hora de Início:</label>
                        <input type="time" name="timeStart" id="timeStart" value="<?php echo htmlspecialchars($timeStart); ?>" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label for="timeEnd">Hora de Fim:</label>
                        <input type="time" name="timeEnd" id="timeEnd" value="<?php echo htmlspecialchars($timeEnd); ?>" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label>Colunas:</label>
                        <div class="form-check">
                            <?php foreach ($available_columns as $column_key => $column_label): ?>
                                <div>
                                    <input type="checkbox" name="columns[]" value="<?php echo $column_key; ?>" id="<?php echo $column_key; ?>" class="form-check-input" <?php if (in_array($column_key, $colunas_selecionadas)) echo 'checked'; ?>>
                                    <label for="<?php echo $column_key; ?>" class="form-check-label"><?php echo $column_label; ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="form-row mt-2">
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i>Aplicar Filtros</button>
                        <a href="teste38.php" class="btn btn-secondary"><i class="fas fa-eraser"></i>Resetar Filtros</a>
                    </div>
                    <div class="col-md-4">
                        <button type="button" onclick="exportToExcel()" class="btn btn-success"><i class="fas fa-file-csv"></i>Exportar para Excel</button>
                        <a href="pdf_filtro.php?filter=<?= urlencode($filter) ?>&local_id=<?= urlencode($local_id) ?>&searchType=<?= urlencode($searchType) ?>&searchQuery=<?= urlencode($searchQuery) ?>&dateType=<?= urlencode($dateType) ?>&dateValue=<?= urlencode($dateValue) ?>&timeType=<?= urlencode($timeType) ?>&timeStart=<?= urlencode($timeStart) ?>&timeEnd=<?= urlencode($timeEnd) ?>&columns[]=<?= implode('&columns[]=', $colunas_selecionadas) ?>" class="btn btn-danger" target="_blank"><i class="far fa-file-pdf"></i>Gerar PDF</a>
                    </div>
                    <!--<div class="col-md-4">
                    <button onclick="location.href='index.php'" class="btn btn-secondary">Voltar</button>

                    </div>-->
                    <div class="col-md-4">
                        <a href="index.php" class="btn btn-secondary"><i class="fas fa-angle-left"></i> Voltar</a>
                        <a href="logout.php" class="btn btn-danger "><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>

                    
                </div>
            </form>
        </div>
        <!-- Tabela -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="theadfixed">
                    <tr>
                        <?php foreach ($colunas_selecionadas as $coluna): ?>
                            <th><?php echo $available_columns[$coluna]; ?></th>
                        <?php endforeach; ?>
                        <?php if ($_SESSION['user_type'] === 'admin'): ?><th>Ações</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pacotes as $pacote): ?>
                        <tr>
                            <?php foreach ($colunas_selecionadas as $coluna): ?>
                                <td>
                                    
                                    <?php
                                    
                                    if ($coluna == 'data_cadastro' || $coluna == 'data_envio' || $coluna == 'data_recebimento' || $coluna == 'data_recebimentolab') {
                                        $data = $pacote[$coluna];
                                        if ($data) {
                                            $dateTime = new DateTime($data);
                                            echo $dateTime->format('d-m-Y H:i');
                                        }
                                    } elseif ($coluna == 'status') {
                                        if ($pacote['status'] == 'cadastrado') {
                                            echo '<span class="badge badge-danger">cadastrado</span>';
                                        } elseif ($pacote['status'] == 'enviado') {
                                            echo '<span class="badge badge-warning">enviado</span>';
                                        } elseif ($pacote['status'] == 'recebido') {
                                            echo '<span class="badge badge-success">recebido</span>';
                                        } elseif ($pacote['status'] == 'recebidolab') {
                                            echo '<span  class="badge badge-primary">recebido LAB</span>';
                                        }
                                    } else {
                                        echo htmlspecialchars($pacote[$coluna]);
                                    }
                                    ?>
                                </td>
                            <?php endforeach; ?>
                            <?php if ($_SESSION['user_type'] === 'admin'): ?>
                                <td class="btn-group-vertical">
                                    <a href="editar_pacote.php?id=<?php echo $pacote['id']; ?>" class="btn btn-primary btn-sm">Editar</a>
                                    <button type="button"  class="btn btn-danger btn-sm" onclick="openDeleteModal(<?php echo $pacote['id']; ?>)">Excluir</button>
                                </td><?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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

    <!-- Modal de Confirmação de Senha -->
    <div class="modal fade" id="confirmPasswordModal" tabindex="-1" role="dialog" aria-labelledby="confirmPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmPasswordModalLabel">Confirmação de Senha</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="deleteForm" method="POST" action="excluir_pacotes.php">
                        <input type="hidden" name="id" id="pacoteIdToDelete">
                        <div class="form-group">
                            <label for="senha_confirmacao">Digite sua senha:</label>
                            <input type="password" class="form-control" id="senha_confirmacao" name="senha_confirmacao" required>
                        </div>
                        <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function exportToExcel() {
            var url = 'export_excel.php?' + $('form').serialize();
            window.location.href = url;
        }

        function generatePDF() {
            var url = 'gerar_pdf.php?' + $('form').serialize();
            
            window.location.href = url;
        }
        function openDeleteModal(pacoteId) {
            $('#pacoteIdToDelete').val(pacoteId);
            $('#confirmPasswordModal').modal('show');
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
