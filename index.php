<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$user_name = $_SESSION['username'];
$local_id = $_SESSION['unidade_id'];

// Definir a consulta SQL com base no tipo de usuário e local
if ($user_type === 'admin') {
    $query = "SELECT p.*, l_envio.nome AS envio_nome, l_lab.nome AS lab_nome, u_envio.usuario AS enviado_por, u_recebimento.usuario AS recebido_por, u_cadastro.usuario AS cadastrado_por, l_cadastro.nome AS cadastro_nome 
              FROM pacotes p
              LEFT JOIN unidadehemopa l_envio ON p.unidade_envio_id = l_envio.id
              LEFT JOIN unidadehemopa l_cadastro ON p.unidade_cadastro_id = l_cadastro.id 
              LEFT JOIN usuarios u_envio ON p.usuario_envio_id = u_envio.id
              LEFT JOIN usuarios u_cadastro ON p.usuario_cadastro_id = u_cadastro.id 
              LEFT JOIN usuarios u_recebimento ON p.usuario_recebimento_id = u_recebimento.id
              LEFT JOIN laboratorio l_lab ON p.lab_id = l_lab.id
              ORDER BY p.data_cadastro DESC";
} elseif ($local_id == 1) {
    $query = "SELECT p.*, l_envio.nome AS envio_nome, l_lab.nome AS lab_nome, u_envio.usuario AS enviado_por, u_recebimento.usuario AS recebido_por, u_cadastro.usuario AS cadastrado_por, l_cadastro.nome AS cadastro_nome 
              FROM pacotes p
              LEFT JOIN unidadehemopa l_envio ON p.unidade_envio_id = l_envio.id
              LEFT JOIN unidadehemopa l_cadastro ON p.unidade_cadastro_id = l_cadastro.id 
              LEFT JOIN usuarios u_envio ON p.usuario_envio_id = u_envio.id
              LEFT JOIN usuarios u_cadastro ON p.usuario_cadastro_id = u_cadastro.id 
              LEFT JOIN usuarios u_recebimento ON p.usuario_recebimento_id = u_recebimento.id
              LEFT JOIN laboratorio l_lab ON p.lab_id = l_lab.id
              WHERE p.status = 'enviado'
              ORDER BY p.data_cadastro DESC";
} else {
    $query = "SELECT p.*, l_envio.nome AS envio_nome, l_lab.nome AS lab_nome, u_envio.usuario AS enviado_por, u_recebimento.usuario AS recebido_por, u_cadastro.usuario AS cadastrado_por, l_cadastro.nome AS cadastro_nome 
              FROM pacotes p
              LEFT JOIN unidadehemopa l_envio ON p.unidade_envio_id = l_envio.id
              LEFT JOIN unidadehemopa l_cadastro ON p.unidade_cadastro_id = l_cadastro.id 
              LEFT JOIN usuarios u_envio ON p.usuario_envio_id = u_envio.id
              LEFT JOIN usuarios u_cadastro ON p.usuario_cadastro_id = u_cadastro.id 
              LEFT JOIN usuarios u_recebimento ON p.usuario_recebimento_id = u_recebimento.id
              LEFT JOIN laboratorio l_lab ON p.lab_id = l_lab.id
              WHERE p.unidade_cadastro_id = :unidade_cadastro_id AND (p.status = 'cadastrado' OR p.status = 'enviado')
              ORDER BY p.data_cadastro DESC";
}

$stmt = $dbconn->prepare($query);

if ($user_type !== 'admin' && $local_id != 1) {
    $stmt->execute([':unidade_cadastro_id' => $local_id]);
} else {
    $stmt->execute();
}

$pacotes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Início</title>
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="sidebar">
        <h3 class="text-center" style="color: #07948A;"><i class="fas fa-home"></i>Menu</h3>
        <a href="cadastro_pacote.php"><i class="fas fa-plus"></i> Cadastrar Amostras</a>
        <a href="envio_pacote.php"><i class="fas fa-paper-plane"></i> Enviar Amostras</a>
        <?php if ($_SESSION['user_type'] === 'admin' || $_SESSION['unidade_id'] === '1'): ?>
            <a href="recebimento.php"><i class="fas fa-inbox"></i> Receber Amostras</a>
        <?php endif; ?>

        <?php if ($_SESSION['user_type'] === 'admin'): ?>
            <a href="relatorio_pendencias.php"><i class="fas fa-file-invoice"></i> Relatorio de Pendencias</a>
            <a href="lista_pacotes.php"><i class="fas fa-vial"></i> Gerenciar Amostras</a>
            <a href="cadastro_usuarios.php"><i class="fas fa-user-plus"></i> Cadastrar Usuário</a>
            <a href="listar.php"><i class="fas fa-users-cog"></i> Gerenciar Usuários</a>
            <a href="cadastrounidade.php"><i class="fas fa-map-marker-alt"></i> Cadastrar Locais</a>
            <a href="gerenciar_locais.php"><i class="fas fa-list"></i> Gerenciar Locais</a>
            <a href="cadastro_lab.php"><i class="fas fa-flask"></i> Cadastrar Laboratório</a>
            <a href="gerenciar_lab.php"><i class="fas fa-list"></i> Gerenciar Laboratório</a>
        <?php endif; ?>
        <a href="mudar_senha.php" class="btn btn-danger btn-lg mt-3"><i class="fas fa-key"></i> Mudar Senha </a>
        <a href="logout.php" class="btn btn-danger mt-3"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    <div class="content">
        <div class="container container-custom">
            <h1 class="text-center mb-4">Bem-vindo ao Sistema de Gerenciamento de Envios e Recebimentos <?php echo ucfirst($user_name); ?></h1>
            <h2 class="text-center mb-4">Você está logado como um Usuário: <?php echo ucfirst($user_type); ?></h2>
        </div>

        <h2 class="text-center mb-4">Amostras no Local</h2>
        <div class="table-wrapper" style="position: relative;" id="managerTable">
            <table class="table table-bordered table-hover table-striped">
                <thead class="theadfixed">
                    <tr>
                        <th>Codigo de Barras</th>
                        <th>Status</th>
                        <th>Descrição</th>
                        <th>Laboratório</th>
                        <th>Data de Cadastro</th>
                        <th>Data de Envio</th>
                        <!--<th>Data de Recebimento</th> -->
                        <th>Local de Cadastro</th>
                        <th>Local de Envio</th>
                        
                        <th>Cadastrado por</th>
                        <th>Enviado por</th>
                        <!-- <th>Recebido por</th> -->
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
                                <!-- <td><?php echo htmlspecialchars($pacote['data_envio']); ?></td> -->
                                <!--  <td><?php echo htmlspecialchars($pacote['data_recebimento']); ?></td> -->
                                <td><?php echo htmlspecialchars($pacote['cadastro_nome']); ?></td>
                                <td><?php echo htmlspecialchars($pacote['envio_nome']); ?></td>
                                
                                <td><?php echo htmlspecialchars($pacote['cadastrado_por']); ?></td>
                                <td><?php echo htmlspecialchars($pacote['enviado_por']); ?></td>
                                <!-- <td><?php echo htmlspecialchars($pacote['recebido_por']); ?></td> -->
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="12">Nenhum pacote encontrado.</td>
                        </tr>
                    <?php endif; ?>
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
