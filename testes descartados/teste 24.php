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
            $conditions[] = "p.data_cadastro = :dateValue";
            break;
        case 'dataEnvio':
            $conditions[] = "p.data_envio = :dateValue";
            break;
        case 'dataRecebimento':
            $conditions[] = "p.data_recebimento = :dateValue";
            break;
        default:
            break;
    }
    $params[':dateValue'] = $dateValue;
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
    <link href="styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container container-custom2">
        <h1 class="text-center mb-4" style="color: #28a745;">Listar Pacotes</h1>
        <?php if ($_SESSION['unidade_id'] != '1' || $_SESSION['user_type'] === 'admin'): ?>
        <a href="cadastro_pacote.php" class="btn btn-custom"><i class="fas fa-plus"></i> Cadastrar Amostras</a>
        <?php endif; ?>
        <form method="GET" action="" class="form-inline mb-4 justify-content-center">
            <div class="form-group mr-3">
                <label for="filter" class="mr-2" style="color: #28a745;">Filtrar por:</label>
                <select name="filter" id="filter" class="form-control">
                    <option value="">Todos</option>
                    <option value="cadastrado" <?php if ($filter == 'cadastrado') echo 'selected'; ?>>Cadastrado</option>
                    <option value="enviados" <?php if ($filter == 'enviados') echo 'selected'; ?>>Enviados</option>
                    <option value="recebidos" <?php if ($filter == 'recebidos') echo 'selected'; ?>>Recebidos</option>
                </select>
            </div>
            <div class="form-group mr-3">
                <label for="local_id" class="mr-2" style="color: #28a745;">Local de Cadastro:</label>
                <select name="local_id" id="local_id" class="form-control">
                    <option value="">Todos</option>
                    <?php foreach ($locais as $local): ?>
                        <option value="<?php echo $local['id']; ?>" <?php if ($local_id == $local['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($local['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group mr-3">
                <label for="searchType" class="mr-2" style="color: #28a745;">Pesquisar por:</label>
                <select name="searchType" id="searchType" class="form-control">
                    <option value="">Selecionar</option>
                    <option value="codigobarras" <?php if ($searchType == 'codigobarras') echo 'selected'; ?>>Código de Barras</option>
                    <option value="usuario_cadastro" <?php if ($searchType == 'usuario_cadastro') echo 'selected'; ?>>Usuário que Cadastrou</option>
                    <option value="usuario_envio" <?php if ($searchType == 'usuario_envio') echo 'selected'; ?>>Usuário que Enviou</option>
                    <option value="usuario_recebimento" <?php if ($searchType == 'usuario_recebimento') echo 'selected'; ?>>Usuário que Recebeu</option>
                    <option value="unidade_envio" <?php if ($searchType == 'unidade_envio') echo 'selected'; ?>>Unidade que Enviou</option>
                    <option value="lab_nome" <?php if ($searchType == 'lab_nome') echo 'selected'; ?>>Nome do Laboratório</option>
                </select>
            </div>
            <div class="form-group mr-3">
                <label for="searchQuery" class="mr-2" style="color: #28a745;">Termo:</label>
                <input type="text" name="searchQuery" id="searchQuery" class="form-control" value="<?php echo htmlspecialchars($searchQuery); ?>">
            </div>
            <div class="form-group mr-3">
                <label for="dateType" class="mr-2" style="color: #28a745;">Tipo de Data:</label>
                <select name="dateType" id="dateType" class="form-control">
                    <option value="">Selecionar</option>
                    <option value="dataCadastro" <?php if ($dateType == 'dataCadastro') echo 'selected'; ?>>Data de Cadastro</option>
                    <option value="dataEnvio" <?php if ($dateType == 'dataEnvio') echo 'selected'; ?>>Data de Envio</option>
                    <option value="dataRecebimento" <?php if ($dateType == 'dataRecebimento') echo 'selected'; ?>>Data de Recebimento</option>
                </select>
            </div>
            <div class="form-group mr-3">
                <label for="dateValue" class="mr-2" style="color: #28a745;">Data:</label>
                <input type="date" name="dateValue" id="dateValue" class="form-control" value="<?php echo htmlspecialchars($dateValue); ?>">
            </div>
            <button type="submit" class="btn btn-custom">Filtrar</button>
        </form>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Status</th>
                        <th>Código de Barras</th>
                        <th>Descrição</th>
                        <th>Data de Envio</th>
                        <th>Data de Recebimento</th>
                        <th>Data de Cadastro</th>
                        <th>Lab</th>
                        <th>Unidade Envio</th>
                        <th>Usuário Envio</th>
                        <th>Usuário Recebimento</th>
                        <th>Usuário Cadastro</th>
                        <th>Unidade Cadastro</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pacotes)): ?>
                        <tr>
                            <td colspan="13" class="text-center">Nenhum pacote encontrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pacotes as $pacote): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($pacote['id']); ?></td>
                                <td><?php echo htmlspecialchars($pacote['status']); ?></td>
                                <td><?php echo htmlspecialchars($pacote['codigobarras']); ?></td>
                                <td><?php echo htmlspecialchars($pacote['descricao']); ?></td>
                                <td><?php echo htmlspecialchars($pacote['data_envio']); ?></td>
                                <td><?php echo htmlspecialchars($pacote['data_recebimento']); ?></td>
                                <td><?php echo htmlspecialchars($pacote['data_cadastro']); ?></td>
                                <td><?php echo htmlspecialchars($pacote['lab_nome']); ?></td>
                                <td><?php echo htmlspecialchars($pacote['envio_nome']); ?></td>
                                <td><?php echo htmlspecialchars($pacote['enviado_por']); ?></td>
                                <td><?php echo htmlspecialchars($pacote['recebido_por']); ?></td>
                                <td><?php echo htmlspecialchars($pacote['cadastrado_por']); ?></td>
                                <td><?php echo htmlspecialchars($pacote['cadastro_nome']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <a href="index.php" class="btn btn-custom">Voltar</a>
    </div>
</body>
</html>
