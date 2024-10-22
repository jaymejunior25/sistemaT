<?php
session_start();
include 'db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Consultar locais vinculados ao usuário
$localQuery = "SELECT ul.local_id, l.nome 
               FROM usuario_local ul
               JOIN unidadehemopa l ON ul.local_id = l.id
               WHERE ul.usuario_id = :usuario_id";

$localStmt = $dbconn->prepare($localQuery);
$localStmt->execute([':usuario_id' => $user_id]);
$locais_vinculados = $localStmt->fetchAll(PDO::FETCH_ASSOC);

// Processar a seleção de unidade
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unidade'])) {
    $_SESSION['unidade_id'] = $_POST['unidade'];
    
    // Obter o nome da unidade selecionada
    $unidade_id = $_SESSION['unidade_id'];
    $query_nome = "SELECT nome FROM unidadehemopa WHERE id = :id";
    $stmt_nome = $dbconn->prepare($query_nome);
    $stmt_nome->execute([':id' => $unidade_id]);
    $unidade_nome = $stmt_nome->fetchColumn();
    $_SESSION['unidade_nome'] = $unidade_nome;
    $sql = "UPDATE user_sessions SET last_activity = NOW(), unidade_id = :unidade_id WHERE user_id = :user_id";
    $stmt = $dbconn->prepare($sql);
    $stmt->execute([':user_id' => $user_id,
                    ':unidade_id' => $unidade_id]);
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selecionar Local</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f2f9f2;
        }
        .select-local-container {
            max-width: 400px;
            margin: auto;
            padding: 2rem;
            border-radius: 8px;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            /* margin-top: 10%; */
        }
        .header-serda {
            max-width: 400px;
            background-color: rgb(38, 168, 147);
            color: #fff; /* Texto branco */
            text-align: center;
            margin: 0 auto;
            padding: 1rem;
            font-size: 20px;
            font-weight: bold;
            border-radius: 8px 8px 0 0;
            margin-top: 8%;
        }
        .btn-custom {
            background-color: rgb(38, 168, 147);
            color: white;
        }
        .btn-custom:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="header-serda">SERDA <br> Sistema de Envio Recebimento e Distribuição de Amostras</div>
    <div class="select-local-container">
        <h2 class="text-center mb-4" style="color: rgb(38, 168, 147);">Selecionar Local</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="unidade" style="color: rgb(38, 168, 147);">Selecione a Unidade:</label>
                <select name="unidade" id="unidade" class="form-control">
                    <?php foreach ($locais_vinculados as $local): ?>
                        <option value="<?php echo $local['local_id']; ?>">
                            <?php echo htmlspecialchars($local['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-custom btn-block">Salvar Unidade</button>
        </form>
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
</body>
</html>
