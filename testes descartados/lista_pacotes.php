<?php
session_start();
include 'db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Obter a lista de locais para o filtro
$stmt = $dbconn->prepare("SELECT id, nome FROM unidadehemopa");
$stmt->execute();
$locais = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Inicializar variáveis para filtros
$filter = '';
$local_id = '';

// Processar filtros
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['filter'])) {
        $filter = $_GET['filter'];
    }
    if (isset($_GET['local_id'])) {
        $local_id = $_GET['local_id'];
    }
}

// Construir a consulta SQL com base nos filtros
$sql = "SELECT p.id, p.descricao, p.data_envio, p.data_recebimento, l.nome AS local_nome, u_envio.usuario AS enviado_por, u_recebimento.usuario AS recebido_por 
        FROM pacotes p 
        LEFT JOIN unidadehemopa l ON p.unidade_envio_id = l.id 
        LEFT JOIN usuarios u_envio ON p.usuario_envio_id = u_envio.id 
        LEFT JOIN usuarios u_recebimento ON p.usuario_recebimento_id = u_recebimento.id";

$conditions = [];
$params = [];

if ($filter == 'enviados') {
    $conditions[] = "p.data_envio IS NOT NULL";
} elseif ($filter == 'recebidos') {
    $conditions[] = "p.data_recebimento IS NOT NULL";
}

if (!empty($local_id)) {
    $conditions[] = "p.unidade_envio_id = :local_id";
    $params[':local_id'] = $local_id;
}

if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$stmt = $dbconn->prepare($sql);
$stmt->execute($params);
$pacotes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Listar Pacotes</title>
</head>
<body>
    <h1>Listar Pacotes</h1>
    <form method="GET" action="">
        <label for="filter">Filtrar por:</label>
        <select name="filter" id="filter">
            <option value="">Todos</option>
            <option value="enviados" <?php if ($filter == 'enviados') echo 'selected'; ?>>Enviados</option>
            <option value="recebidos" <?php if ($filter == 'recebidos') echo 'selected'; ?>>Recebidos</option>
        </select>
        
        <label for="local_id">Local de Envio:</label>
        <select name="local_id" id="local_id">
            <option value="">Todos</option>
            <?php foreach ($locais as $local): ?>
                <option value="<?php echo $local['id']; ?>" <?php if ($local_id == $local['id']) echo 'selected'; ?>>
                    <?php echo $local['nome']; ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <button type="submit">Filtrar</button>
    </form>
    
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Descrição</th>
                <th>Data de Envio</th>
                <th>Data de Recebimento</th>
                <th>Local de Envio</th>
                <th>Enviado por</th>
                <th>Recebido por</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($pacotes) > 0): ?>
                <?php foreach ($pacotes as $pacote): ?>
                    <tr>
                        <td><?php echo $pacote['id']; ?></td>
                        <td><?php echo $pacote['descricao']; ?></td>
                        <td><?php echo $pacote['data_envio']; ?></td>
                        <td><?php echo $pacote['data_recebimento']; ?></td>
                        <td><?php echo $pacote['local_nome']; ?></td>
                        <td><?php echo $pacote['enviado_por']; ?></td>
                        <td><?php echo $pacote['recebido_por']; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">Nenhum pacote encontrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <a href="logout.php" class="btn btn-danger btn-lg mt-3">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    <a href="index.php">Voltar</a>
</body>
</html>
