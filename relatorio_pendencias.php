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
$data_inicio = '';
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
    if (isset($_GET['data_inicio'])) {
        $data_inicio = $_GET['data_inicio'];
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
    $conditions[] = "p.data_envio IS NOT NULL";
} elseif ($filter == 'recebidos') {
    $conditions[] = "p.data_recebimento IS NOT NULL";
} elseif ($filter == 'cadastrado') {
    $conditions[] = "p.data_cadastro IS NOT NULL";
}

if (!empty($local_id)) {
    $conditions[] = "p.unidade_envio_id = :local_id";
    $params[':local_id'] = $local_id;
}

if (!empty($searchType) && !empty($searchQuery)) {
    $queryParam = '%' . $searchQuery . '%';
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
            $conditions[] = "u_cadastro.usuario LIKE :query";
            break;
        case 'usuario_envio':
            $conditions[] = "u_envio.usuario LIKE :query";
            break;
        case 'usuario_recebimento':
            $conditions[] = "u_recebimento.usuario LIKE :query";
            break;
        case 'unidade_envio':
            $conditions[] = "l_envio.nome LIKE :query";
            break;
        case 'data_cadastro':
            $conditions[] = "TO_CHAR(p.data_cadastro, 'DD-MM-YYYY') LIKE :query";
            break;
        case 'data_envio':
            $conditions[] = "TO_CHAR(p.data_envio, 'DD-MM-YYYY') LIKE :query";
            break;
        case 'data_recebimento':
            $conditions[] = "TO_CHAR(p.data_recebimento, 'DD-MM-YYYY') LIKE :query";
            break;
        case 'lab_nome':
            $conditions[] = "l_lab.nome LIKE :query";
            break;  
        default:
            break;
    }
    $params[':query'] = $queryParam;
}

if (!empty($data_inicio)) {
    $conditions[] = "p.data_cadastro >= :data_inicio";
    $params[':data_inicio'] = $data_inicio;
}

if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " AND data_recebimento IS NULL ORDER BY p.data_cadastro DESC";  // Ordenar por data de cadastro decrescente

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
        <h1 class="text-center mb-4" style="color: #28a745;">Listar Amostras</h1>
        
        
        <!-- Formulário de seleção de colunas e data de início -->
        <form method="GET" action="relatorio_pendencias.php" class="mb-4">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="data_inicio">Data de Início:</label>
                    <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?php echo htmlspecialchars($data_inicio); ?>">
                </div>
                <div class="form-group col-md-8">
                    <label for="columns">Selecionar Colunas:</label>
                    <div class="form-check">
                        <?php foreach ($available_columns as $column => $label): ?>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="columns[]" id="column_<?php echo $column; ?>" value="<?php echo $column; ?>" checked <?php if (in_array($column, $colunas_selecionadas)) echo 'checked'; ?>>
                                <label class="form-check-label" for="column_<?php echo $column; ?>"><?php echo htmlspecialchars($label); ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-custom"><i class="fas fa-filter"></i> Aplicar Seleção</button>
        </form>

        <!-- Exibir Tabela de Pacotes -->
        <?php if (!empty($colunas_selecionadas)): ?>
             <!-- Formulário de filtros -->
             <form method="GET" action="relatorio_pendencias.php" class="form-inline justify-content-center">
                <div class="form-group mb-2">
                    <label for="filter" class="mr-2">Filtrar:</label>
                    <select name="filter" id="filter" class="form-control">
                        <option value="">Todos</option>
                        <option value="enviados" <?php if ($filter == 'enviados') echo 'selected'; ?>>Enviados</option>
                        <option value="recebidos" <?php if ($filter == 'recebidos') echo 'selected'; ?>>Recebidos</option>
                        <option value="cadastrado" <?php if ($filter == 'cadastrado') echo 'selected'; ?>>Cadastrados</option>
                    </select>
                </div>
                <div class="form-group mb-2 ml-2">
                    <label for="local_id" class="mr-2">Local:</label>
                    <select name="local_id" id="local_id" class="form-control">
                        <option value="">Todos</option>
                        <?php foreach ($locais as $local): ?>
                            <option value="<?php echo $local['id']; ?>" <?php if ($local_id == $local['id']) echo 'selected'; ?>><?php echo htmlspecialchars($local['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mb-2 ml-2">
                    <label for="searchType" class="mr-2">Buscar por:</label>
                    <select name="searchType" id="searchType" class="form-control">
                        <option value="codigobarras" <?php if ($searchType == 'codigobarras') echo 'selected'; ?>>Codigo de Barras</option>
                        <option value="usuario_cadastro" <?php if ($searchType == 'usuario_cadastro') echo 'selected'; ?>>Usuario de Cadastro</option>
                        <option value="usuario_envio" <?php if ($searchType == 'usuario_envio') echo 'selected'; ?>>Usuario de Envio</option>
                        <option value="usuario_recebimento" <?php if ($searchType == 'usuario_recebimento') echo 'selected'; ?>>Usuario de Recebimento</option>
                        <option value="unidade_envio" <?php if ($searchType == 'unidade_envio') echo 'selected'; ?>>Unidade de Envio</option>
                        <option value="data_cadastro" <?php if ($searchType == 'data_cadastro') echo 'selected'; ?>>Data de Cadastro</option>
                        <option value="data_envio" <?php if ($searchType == 'data_envio') echo 'selected'; ?>>Data de Envio</option>
                        <option value="data_recebimento" <?php if ($searchType == 'data_recebimento') echo 'selected'; ?>>Data de Recebimento</option>
                        <option value="lab_nome" <?php if ($searchType == 'lab_nome') echo 'selected'; ?>>Laboratorio</option>
                    </select>
                </div>
                <div class="form-group mb-2 ml-2">
                    <label for="searchQuery" class="mr-2">Buscar:</label>
                    <input type="text" name="searchQuery" id="searchQuery" class="form-control" value="<?php echo htmlspecialchars($searchQuery); ?>">
                </div>
                <input type="hidden" name="data_inicio" value="<?php echo htmlspecialchars($data_inicio); ?>">
                <?php foreach ($colunas_selecionadas as $column): ?>
                    <input type="hidden" name="columns[]" value="<?php echo htmlspecialchars($column); ?>">
                <?php endforeach; ?>
                <button type="submit" class="btn btn-custom mb-2 ml-2"><i class="fas fa-search"></i> Filtrar</button>
            </form>
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead class="theadfixed">
                        <tr>
                            <?php foreach ($colunas_selecionadas as $column): ?>
                                <th><?php echo htmlspecialchars($available_columns[$column]); ?></th>
                            <?php endforeach; ?>
                            <?php if ($_SESSION['user_type'] === 'admin'): ?>
                                <th>Ações</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pacotes)): ?>
                            <?php foreach ($pacotes as $pacote): ?>
                                <tr>
                                    <?php foreach ($colunas_selecionadas as $column): ?>
                                        <td>
                                            <?php
                                            switch ($column) {
                                                case 'codigobarras':
                                                    echo htmlspecialchars($pacote['codigobarras']);
                                                    break;
                                                case 'status':
                                                    if ($pacote['status'] == 'enviado') {
                                                        echo '<span class="badge badge-warning">Enviado</span>';
                                                    } elseif ($pacote['status'] == 'recebido') {
                                                        echo '<span class="badge badge-success">Recebido</span>';
                                                    } else {
                                                        echo '<span class="badge badge-danger">cadastrado</span>';
                                                    }
                                                    break;
                                                case 'descricao':
                                                    echo htmlspecialchars($pacote['descricao']);
                                                    break;
                                                case 'lab_nome':
                                                    echo htmlspecialchars($pacote['lab_nome']);
                                                    break;
                                                case 'data_cadastro':
                                                    echo htmlspecialchars($pacote['data_cadastro']);
                                                    break;
                                                case 'data_envio':
                                                    echo htmlspecialchars($pacote['data_envio']);
                                                    break;
                                                case 'data_recebimento':
                                                    echo htmlspecialchars($pacote['data_recebimento']);
                                                    break;
                                                case 'envio_nome':
                                                    echo htmlspecialchars($pacote['envio_nome']);
                                                    break;
                                                case 'cadastrado_por':
                                                    echo htmlspecialchars($pacote['cadastrado_por']);
                                                    break;
                                                case 'enviado_por':
                                                    echo htmlspecialchars($pacote['enviado_por']);
                                                    break;
                                                case 'recebido_por':
                                                    echo htmlspecialchars($pacote['recebido_por']);
                                                    break;
                                                default:
                                                    break;
                                            }
                                            ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <?php if ($_SESSION['user_type'] === 'admin'): ?>
                                        <td>
                                            <a href="editar_pacote.php?id=<?php echo $pacote['id']; ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>
                                            <a href="excluir_pacote.php?id=<?php echo $pacote['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este pacote?');"><i class="fas fa-trash"></i></a>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?php echo count($colunas_selecionadas) + 1; ?>" class="text-center">Nenhum pacote encontrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Exibir o número de resultados -->
            <div class="text-center mb-4">
                <p><strong>Total de Amostras:</strong> <?php echo count($pacotes); ?></p>
            </div>
            <form method="post" action="generate_pdf.php" target="_blank">
                <input type="hidden" name="data_inicio" value="<?php echo htmlspecialchars($data_inicio); ?>">
                <?php foreach ($colunas_selecionadas as $coluna): ?>
                    <input type="hidden" name="colunas[]" value="<?php echo htmlspecialchars($coluna); ?>">
                <?php endforeach; ?>
                <button type="submit" class="btn btn-danger"><i class="far fa-file-pdf"></i> Baixar PDF</button>
            </form>
            
           
        <?php endif; ?>
        <div class="text-center mt-3">
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-angle-left"></i> Voltar</a>
            </div>
        <a href="logout.php" class="btn btn-danger btn-lg mt-3"><i class="fas fa-sign-out-alt"></i> Logout</a>
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
                time = setTimeout(logout, 900000);
            }
        };

        inactivityTime();
    </script>
</body>
</html>

