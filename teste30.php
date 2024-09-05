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
$timeValue = '';
$colunas_selecionadas = [];

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
    if (isset($_GET['timeValue'])) {
        $timeValue = $_GET['timeValue'];
    }
    if (isset($_GET['columns'])) {
        $colunas_selecionadas = $_GET['columns'];
    }
}
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
            }else {
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

if (!empty($timeType) && !empty($timeValue)) {
    switch ($timeType) {
        case 'horaEnvio':
            $conditions[] = "TIME(p.data_envio) = :timeValue";
            break;
        case 'horaRecebimento':
            $conditions[] = "TIME(p.data_recebimento) = :timeValue";
            break;
        default:
            break;
    }
    $params[':timeValue'] = $timeValue;
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
        .side-panel {
            height: 100%;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            background-color: #f8f9fa;
            padding-top: 20px;
        }

        .main-content {
            margin-left: 270px;
            padding: 20px;
        }

        .table-responsive {
            max-height: 600px; /* Ajuste conforme necessário */
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="side-panel">
        <h4>Filtrar Pacotes</h4>
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
                        <option value="<?= $local['id'] ?>" <?= ($local_id == $local['id']) ? 'selected' : '' ?>><?= $local['nome'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="searchType">Tipo de Pesquisa:</label>
                <select name="searchType" id="searchType" class="form-control">
                    <option value="">Selecione</option>
                    <option value="codigobarras" <?= ($searchType == 'codigobarras') ? 'selected' : '' ?>>Código de Barras</option>
                    <option value="usuario_cadastro" <?= ($searchType == 'usuario_cadastro') ? 'selected' : '' ?>>Usuário Cadastro</option>
                    <option value="usuario_envio" <?= ($searchType == 'usuario_envio') ? 'selected' : '' ?>>Usuário Envio</option>
                    <option value="usuario_recebimento" <?= ($searchType == 'usuario_recebimento') ? 'selected' : '' ?>>Usuário Recebimento</option>
                    <option value="unidade_envio" <?= ($searchType == 'unidade_envio') ? 'selected' : '' ?>>Unidade de Envio</option>
                    <option value="lab_nome" <?= ($searchType == 'lab_nome') ? 'selected' : '' ?>>Laboratório</option>
                </select>
            </div>

            <div class="form-group">
                <label for="searchQuery">Pesquisa:</label>
                <input type="text" name="searchQuery" id="searchQuery" class="form-control" value="<?= $searchQuery ?>">
            </div>

            <div class="form-group">
                <label for="dateType">Tipo de Data:</label>
                <select name="dateType" id="dateType" class="form-control">
                    <option value="">Selecione</option>
                    <option value="dataCadastro" <?= ($dateType == 'dataCadastro') ? 'selected' : '' ?>>Data de Cadastro</option>
                    <option value="dataEnvio" <?= ($dateType == 'dataEnvio') ? 'selected' : '' ?>>Data de Envio</option>
                    <option value="dataRecebimento" <?= ($dateType == 'dataRecebimento') ? 'selected' : '' ?>>Data de Recebimento</option>
                </select>
            </div>

            <div class="form-group">
                <label for="dateValue">Data:</label>
                <input type="date" name="dateValue" id="dateValue" class="form-control" value="<?= $dateValue ?>">
            </div>

            <div class="form-group">
                <label for="timeType">Tipo de Hora:</label>
                <select name="timeType" id="timeType" class="form-control">
                    <option value="">Selecione</option>
                    <option value="horaEnvio" <?= ($timeType == 'horaEnvio') ? 'selected' : '' ?>>Hora de Envio</option>
                    <option value="horaRecebimento" <?= ($timeType == 'horaRecebimento') ? 'selected' : '' ?>>Hora de Recebimento</option>
                </select>
            </div>

            <div class="form-group">
                <label for="timeValue">Hora:</label>
                <input type="time" name="timeValue" id="timeValue" class="form-control" value="<?= $timeValue ?>">
            </div>

            <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
        </form>

        <h4>Selecionar Colunas</h4>
        <form method="get" action="">
            <input type="hidden" name="filter" value="<?= $filter ?>">
            <input type="hidden" name="local_id" value="<?= $local_id ?>">
            <input type="hidden" name="searchType" value="<?= $searchType ?>">
            <input type="hidden" name="searchQuery" value="<?= $searchQuery ?>">
            <input type="hidden" name="dateType" value="<?= $dateType ?>">
            <input type="hidden" name="dateValue" value="<?= $dateValue ?>">
            <input type="hidden" name="timeType" value="<?= $timeType ?>">
            <input type="hidden" name="timeValue" value="<?= $timeValue ?>">

            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="selectAll" onclick="selectAllCheckboxes(this)">
                <label class="form-check-label" for="selectAll">
                    Selecionar Todas
                </label>
            </div>
            <?php foreach ($available_columns as $column => $label): ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="columns[]" id="<?= $column ?>" value="<?= $column ?>" <?= in_array($column, $colunas_selecionadas) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="<?= $column ?>">
                        <?= $label ?>
                    </label>
                </div>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-primary">Aplicar Colunas</button>
        </form>
    </div>

    <div class="main-content">
        <h1>Pacotes</h1>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <?php foreach ($colunas_selecionadas as $column): ?>
                            <th><?= $available_columns[$column] ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pacotes as $pacote): ?>
                        <tr>
                            <?php foreach ($colunas_selecionadas as $column): ?>
                                <td><?= $pacote[$column] ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        function selectAllCheckboxes(source) {
            checkboxes = document.getElementsByName('columns[]');
            for(var i=0, n=checkboxes.length;i<n;i++) {
                checkboxes[i].checked = source.checked;
            }
        }
    </script>
</body>
</html>
