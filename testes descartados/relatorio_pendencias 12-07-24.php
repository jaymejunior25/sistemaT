<?php
session_start();
include 'db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['unidade_id'] != '1' && $_SESSION['user_type'] != 'admin') {
    header('Location: index.php');
    exit();
}

$pacotes = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data_inicio = $_POST['data_inicio'];


    $sql = "SELECT p.id, p.status, p.codigobarras, p.descricao, p.data_envio, p.data_recebimento, p.data_cadastro, l_envio.nome AS envio_nome, l_lab.nome AS lab_nome, u_envio.usuario AS enviado_por, u_recebimento.usuario AS recebido_por,
    u_cadastro.usuario AS cadastrado_por, l_cadastro.nome AS cadastro_nome 
    FROM pacotes p 
    LEFT JOIN unidadehemopa l_envio ON p.unidade_envio_id = l_envio.id 
    LEFT JOIN unidadehemopa l_cadastro ON p.unidade_cadastro_id = l_cadastro.id 
    LEFT JOIN usuarios u_cadastro ON p.usuario_cadastro_id = u_cadastro.id 
    LEFT JOIN usuarios u_envio ON p.usuario_envio_id = u_envio.id 
    LEFT JOIN usuarios u_recebimento ON p.usuario_recebimento_id = u_recebimento.id
    LEFT JOIN laboratorio l_lab ON p.lab_id = l_lab.id
    WHERE data_envio >= :data_inicio AND data_recebimento IS NULL 
    ORDER BY p.data_cadastro DESC ";

    // Consultar pacotes enviados mas não recebidos a partir da data especificada
    //$stmt = $dbconn->prepare('SELECT * FROM pacotes WHERE data_envio >= :data_inicio AND data_recebimento IS NULL');

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
    <div class="container container-customlistas">
        <h1 class="text-center">Relatório de Amostras Não Recebidas</h1>
        <form method="post" action="">
            <div class="form-group">
                <label for="data_inicio">Data de Início</label>
                <input type="date" name="data_inicio" id="data_inicio" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary"> <i class="fas fa-file-invoice"></i>Gerar Relatório</button>
            <a href="index.php" class="btn btn-secondary"> <i class="fas fa-angle-left"></i> Voltar</a>
        </form>
        <?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>

        <div class="table-wrapper" style="position: relative;" id="managerTable">  
        <table class="table table-bordered table-hover table-striped">
            <thead class="theadfixed">
                <tr>
                    <th>Codigo de <br>Barras</th>
                    <th>Status</th>
                    <th>Descrição</th>
                    <th>Laboratorio</th>
                    <th>Data de <br>Cadastro</th>
                    <th>Data de <br>Envio</th>
                    
                    <th>Local de <br>Cadastro</th>
                    <th>Local de <br>Envio</th>
                    <th>Cadastrado por</th>
                    <th>Enviado por</th>
                    
                </tr>
            </thead>
            <tbody>
                <?php if (count($pacotes) > 0): ?>
                    <?php foreach ($pacotes as $pacote): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($pacote['codigobarras']); ?></td>
                            <td>
                                <?php if ($pacote['status'] == 'cadastrado'): ?>
                                    <span class="badge badge-danger">cadastrado</span>
                                <?php elseif($pacote['status'] == 'enviado'): ?>
                                    <span class="badge badge-warning">enviado</span>
                                <?php elseif($pacote['status'] == 'recebido'): ?>
                                    <span class="badge badge-success">recebido</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($pacote['descricao']); ?></td>
                            <td><?php echo htmlspecialchars($pacote['lab_nome']); ?></td>
                            <td><?php echo htmlspecialchars(date("d-m-Y", strtotime($pacote['data_cadastro']))); ?></td>
                            <td><?php if($pacote['data_envio']) {echo htmlspecialchars(date("d-m-Y", strtotime($pacote['data_envio'])));}; ?></td>
                            
                            <td><?php echo htmlspecialchars($pacote['cadastro_nome']); ?></td>
                            <td><?php echo htmlspecialchars($pacote['envio_nome']); ?></td>
                            <td><?php echo htmlspecialchars($pacote['cadastrado_por']); ?></td>
                            <td><?php echo htmlspecialchars($pacote['enviado_por']); ?></td>
                            
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">Nenhum pacote encontrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        
    </div>
    <form method="post" action="generate_pdf.php" target="_blank">
                <input type="hidden" name="data_inicio" value="<?php echo htmlspecialchars($data_inicio); ?>">
                <button type="submit" class="btn btn-danger"> <i class="far fa-file-pdf"></i> Baixar PDF</button>
            </form>
        <div class="text-center mt-3">
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-angle-left"></i> Voltar</a>
        </div>
 
        <?php endif; ?>

        <a href="logout.php" class="btn btn-danger btn-lg mt-3"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
        
    <div class="fixed-bottom toggle-footer cursor_to_down" id="footer_fixed" >
            <!-- style="margin-top:50px;" -->
            <div class="fixed-bottom border-top bg-light text-center footer-content p-2" style="z-index:4; ">
                <!-- w3-card  -->
                <div class="footer-text" >
                    Desenvolvido com &#128151; por Gerencia de Informatica - GETIN <br>
                    <a class="text-reset fw-bold" href="http://www.hemopa.pa.gov.br/site/">© Todos os direitos reservados 2024 Hemopa.</a>
                </div>
            </div>
        </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
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
