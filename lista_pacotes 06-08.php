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
    'data_recebimento' => 'Data de Recebimento',
    'envio_nome' => 'Local de Envio',
    'cadastrado_por' => 'Cadastrado por',
    'enviado_por' => 'Enviado por',
    'recebido_por' => 'Recebido por'
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
        $colunas_selecionadas = array_keys($available_columns); // Todas as colunas selecionadas por padrão
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
    
    <style>
        body {
            margin: 0;
            padding: 0;
        }
        
        .side-panel {
            height: 92vh; /* Ocupa toda a altura da tela */
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            background-color: #e0f2f1; /* Tom de verde claro */
            padding-top: 20px;
            overflow-y: auto;
        }


        .main-content {
            
            margin-left: 270px;
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
        .th.fixed {
            top: 0;
            z-index: 2;
            position: sticky;
            background-color: white;
        }
        .theadfixed{
            position: sticky;
            top: 0;
            background-color: rgb(38, 168, 147);
            color: aliceblue;
        }
    </style>
</head>
<body>
    <div class="side-panel">
        <h4>Filtrar Amostras</h4>
        <button onclick="location.href='index.php'" class="btn btn-secondary btn-block">Voltar</button>
        <form method="get" action="">
            <div class="form-group">
                <label for="filter">Filtrar por Status:</label>
                <select name="filter" id="filter" class="form-control">
                    <option value="">Todos</option>
                    <option value="enviados" <?= ($filter == 'enviados') ? 'selected' : '' ?>>Enviados</option>
                    <option value="recebidos" <?= ($filter == 'recebidos') ? 'selected' : '' ?>>Recebidos</option>
                    <option value="cadastrado" <?= ($filter == 'cadastrado') ? 'selected' : '' ?>>Cadastrados</option>
                </select>
            </div>

            <div class="form-group">
                <label for="local_id">Local:</label>
                <select name="local_id" id="local_id" class="form-control">
                    <option value="">Todos</option>
                    <?php foreach ($locais as $local): ?>
                        <option value="<?= $local['id'] ?>" <?= ($local_id == $local['id']) ? 'selected' : '' ?>><?= htmlspecialchars($local['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="searchType">Tipo de Busca:</label>
                <select name="searchType" id="searchType" class="form-control">
                    <option value="">Selecionar</option>
                    <option value="codigobarras" <?= ($searchType == 'codigobarras') ? 'selected' : '' ?>>Codigo de Barras</option>
                    <option value="usuario_cadastro" <?= ($searchType == 'usuario_cadastro') ? 'selected' : '' ?>>Usuario Cadastro</option>
                    <option value="usuario_envio" <?= ($searchType == 'usuario_envio') ? 'selected' : '' ?>>Usuario Envio</option>
                    <option value="usuario_recebimento" <?= ($searchType == 'usuario_recebimento') ? 'selected' : '' ?>>Usuario Recebimento</option>
                    <option value="unidade_envio" <?= ($searchType == 'unidade_envio') ? 'selected' : '' ?>>Unidade Envio</option>
                    <option value="lab_nome" <?= ($searchType == 'lab_nome') ? 'selected' : '' ?>>Laboratorio</option>
                </select>
            </div>

            <div class="form-group">
                <label for="searchQuery">Busca:</label>
                <input type="text" name="searchQuery" id="searchQuery" class="form-control" value="<?= htmlspecialchars($searchQuery) ?>">
            </div>

            <div class="form-group">
                <label for="dateType">Tipo de Data:</label>
                <select name="dateType" id="dateType" class="form-control">
                    <option value="">Selecionar</option>
                    <option value="dataCadastro" <?= ($dateType == 'dataCadastro') ? 'selected' : '' ?>>Data de Cadastro</option>
                    <option value="dataEnvio" <?= ($dateType == 'dataEnvio') ? 'selected' : '' ?>>Data de Envio</option>
                    <option value="dataRecebimento" <?= ($dateType == 'dataRecebimento') ? 'selected' : '' ?>>Data de Recebimento</option>
                </select>
            </div>

            <div class="form-group">
                <label for="dateValue">Data:</label>
                <input type="date" name="dateValue" id="dateValue" class="form-control" value="<?= htmlspecialchars($dateValue) ?>">
            </div>

            <div class="form-group">
                <label for="timeType">Tipo de Hora:</label>
                <select name="timeType" id="timeType" class="form-control">
                    <option value="">Selecionar</option>
                    <option value="horaEnvio" <?= ($timeType == 'horaEnvio') ? 'selected' : '' ?>>Hora de Envio</option>
                    <option value="horaRecebimento" <?= ($timeType == 'horaRecebimento') ? 'selected' : '' ?>>Hora de Recebimento</option>
                </select>
            </div>

            <div class="form-group">
                <label for="timeStart">Hora de Início:</label>
                <input type="time" name="timeStart" id="timeStart" class="form-control" value="<?= htmlspecialchars($timeStart) ?>">
            </div>

            <div class="form-group">
                <label for="timeEnd">Hora de Fim:</label>
                <input type="time" name="timeEnd" id="timeEnd" class="form-control" value="<?= htmlspecialchars($timeEnd) ?>">
            </div>

            <div class="form-group">
                <label>Colunas a Exibir:</label><br>
                <?php foreach ($available_columns as $column_key => $column_label): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="columns[]" id="<?= $column_key ?>" value="<?= $column_key ?>" <?= in_array($column_key, $colunas_selecionadas) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="<?= $column_key ?>"><?= $column_label ?></label>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="pdf_filtro.php?filter=<?= urlencode($filter) ?>&local_id=<?= urlencode($local_id) ?>&searchType=<?= urlencode($searchType) ?>&searchQuery=<?= urlencode($searchQuery) ?>&dateType=<?= urlencode($dateType) ?>&dateValue=<?= urlencode($dateValue) ?>&timeType=<?= urlencode($timeType) ?>&timeStart=<?= urlencode($timeStart) ?>&timeEnd=<?= urlencode($timeEnd) ?>&columns[]=<?= implode('&columns[]=', $colunas_selecionadas) ?>" class="btn btn-success mt-3">Exportar para PDF</a>
        </form>
        <br>
        
        <button onclick="location.href='index.php'" class="btn btn-secondary btn-block">Voltar</button>
        <button onclick="location.href='logout.php'" class="btn btn-danger btn-block">Logout</button>
    </div>

    <div class="main-content">
        <h2>Listagem de Amostars</h2>
        <p><strong>Total de Amostras:</strong> <?php echo count($pacotes); ?></p>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="theadfixed">
                    <tr>
                        <?php foreach ($colunas_selecionadas as $coluna): ?>
                            <th><?= $available_columns[$coluna] ?></th>
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
                                    // Formatação específica para campos de data e hora
                                    if ($coluna == 'data_cadastro' || $coluna == 'data_envio' || $coluna == 'data_recebimento') {
                                        echo date('d-m-Y H:i', strtotime($pacote[$coluna]));
                                    } else { 
                                       echo htmlspecialchars($pacote[$coluna]);
                                    }
                                    ?>
                                </td>
                            <?php endforeach; ?>
                            <?php if ($_SESSION['user_type'] === 'admin'): ?><td>
                                    <a href="editar_pacote.php?id=<?php echo $pacote['id']; ?>" class="btn btn-primary btn-sm">Editar</a>
                                    <button type="button"  class="btn btn-danger btn-sm" onclick="openDeleteModal(<?php echo $pacote['id']; ?>)">Excluir</button>
                                </td><?php endif; ?>
                            <?php endforeach; ?>
                            

                        </tr>

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

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
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
