<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['unidade_id'] != '1' && $_SESSION['user_type'] != 'admin') {
    header('Location: index.php');
    exit();
}

$pacotes = [];
$colunas_selecionadas = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data_inicio = $_POST['data_inicio'];
    $colunas_selecionadas = isset($_POST['colunas']) ? $_POST['colunas'] : [];
    // Criar a consulta SQL com base nas colunas selecionadas
    $colunas = [
        'codigobarras' => 'p.codigobarras',
        'status' => 'p.status',
        'descricao' => 'p.descricao',
        'lab_nome' => 'l_lab.nome AS lab_nome',
        'data_cadastro' => 'p.data_cadastro',
        'data_envio' => 'p.data_envio',
        'cadastro_nome' => 'l_cadastro.nome AS cadastro_nome',
        'envio_nome' => 'l_envio.nome AS envio_nome',
        'cadastrado_por' => 'u_cadastro.usuario AS cadastrado_por',
        'enviado_por' => 'u_envio.usuario AS enviado_por'
    ];

    $colunas_selecionadas_sql = array_intersect_key($colunas, array_flip($colunas_selecionadas));

    $sql = "SELECT " . implode(", ", $colunas_selecionadas_sql) . " 
            FROM pacotes p 
            LEFT JOIN unidadehemopa l_envio ON p.unidade_envio_id = l_envio.id 
            LEFT JOIN unidadehemopa l_cadastro ON p.unidade_cadastro_id = l_cadastro.id 
            LEFT JOIN usuarios u_cadastro ON p.usuario_cadastro_id = u_cadastro.id 
            LEFT JOIN usuarios u_envio ON p.usuario_envio_id = u_envio.id 
            LEFT JOIN laboratorio l_lab ON p.lab_id = l_lab.id
            WHERE data_envio >= :data_inicio AND data_recebimento IS NULL 
            ORDER BY p.data_cadastro DESC";

    $stmt = $dbconn->prepare($sql);
    $stmt->execute(['data_inicio' => $data_inicio]);
    $pacotes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Pacotes Não Recebidos</title>
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <div class="container container-custom2 ">
        <h1 class="text-center">Relatório de Amostras Não Recebidas</h1>
        <form method="post" action="">
            <div class="form-group">
                <label for="data_inicio">Data de Início</label>
                <input type="date" name="data_inicio" id="data_inicio" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Selecionar Colunas:</label><br>
                <input type="checkbox" name="colunas[]" value="codigobarras" checked> Código de Barras<br>
                <input type="checkbox" name="colunas[]" value="status" checked> Status<br>
                <input type="checkbox" name="colunas[]" value="descricao" checked> Descrição<br>
                <input type="checkbox" name="colunas[]" value="lab_nome" checked> Laboratório<br>
                <input type="checkbox" name="colunas[]" value="data_cadastro" checked> Data de Cadastro<br>
                <input type="checkbox" name="colunas[]" value="data_envio" checked> Data de Envio<br>
                <input type="checkbox" name="colunas[]" value="cadastro_nome" checked> Local de Cadastro<br>
                <input type="checkbox" name="colunas[]" value="envio_nome" checked> Local de Envio<br>
                <input type="checkbox" name="colunas[]" value="cadastrado_por" checked> Cadastrado por<br>
                <input type="checkbox" name="colunas[]" value="enviado_por" checked> Enviado por<br>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-file-invoice"></i> Gerar Relatório</button>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-angle-left"></i> Voltar</a>
        </form>

        <?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
            <div class="table-wrapper" style="position: relative;" id="managerTable">  
                <table class="table table-bordered table-hover table-striped">
                    <thead class="theadfixed">
                        <tr>
                            <?php foreach ($colunas_selecionadas as $coluna): ?>
                                <th><?php echo ucwords(str_replace('_', ' ', $coluna)); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($pacotes) > 0): ?>
                            <?php foreach ($pacotes as $pacote): ?>
                                <tr>
                                    <?php foreach ($colunas_selecionadas as $coluna): ?>
                                        <td><?php echo htmlspecialchars($pacote[$coluna]); ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?php echo count($colunas_selecionadas); ?>">Nenhum pacote encontrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <form method="post" action="generate_pdf.php" target="_blank">
                <input type="hidden" name="data_inicio" value="<?php echo htmlspecialchars($data_inicio); ?>">
                <?php foreach ($colunas_selecionadas as $coluna): ?>
                    <input type="hidden" name="colunas[]" value="<?php echo htmlspecialchars($coluna); ?>">
                <?php endforeach; ?>
                <button type="submit" class="btn btn-danger"><i class="far fa-file-pdf"></i> Baixar PDF</button>
            </form>
            <div class="text-center mt-3">
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-angle-left"></i> Voltar</a>
            </div>
        <?php endif; ?>

        
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
