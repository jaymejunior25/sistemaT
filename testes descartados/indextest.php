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


// Buscar pacotes cadastrados no mesmo local do usuário

$stmt = $dbconn->prepare("SELECT p.*,  l_envio.nome AS envio_nome, u_envio.usuario AS enviado_por, u_recebimento.usuario AS recebido_por,
        u_cadastro.usuario AS cadastrado_por, l_cadastro.nome AS cadastro_nome 
                        FROM pacotes p
                        LEFT JOIN unidadehemopa l_envio ON p.unidade_envio_id = l_envio.id
                        LEFT JOIN unidadehemopa l_cadastro ON p.unidade_cadastro_id = l_cadastro.id 
                        LEFT JOIN usuarios u_envio ON p.usuario_envio_id = u_envio.id
                        LEFT JOIN usuarios u_cadastro ON p.usuario_cadastro_id = u_cadastro.id 
                        LEFT JOIN usuarios u_recebimento ON p.usuario_recebimento_id = u_recebimento.id
                        WHERE p.unidade_cadastro_id = :unidade_cadastro_id");
$stmt->execute([':unidade_cadastro_id' => $local_id]);
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
    <style>
        .table-wrapper {
            max-height: 550px; /* Ajuste conforme necessário */
            overflow-y: auto;
        }
        footer {
            text-align: center;
            padding: 10px;
            background-color: #f1f1f1;
            position: absolute;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>

<body>
    <div class="container-fluid container-custom">
        <div class="row">
            <nav class="col-md-2 d-none d-md-block bg-light sidebar"> 
                <div class="sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">
                                <i class="fas fa-home"></i>
                                Início
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="cadastro_pacote.php">
                                <i class="fas fa-plus"></i>
                                Cadastrar Amostras
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="envio_pacote.php">
                                <i class="fas fa-paper-plane"></i>
                                Enviar Amostras
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="recebimento.php">
                                <i class="fas fa-inbox"></i>
                                Receber Amostras
                            </a>
                        </li>
                        <?php if ($_SESSION['user_type'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="relatorio_pendencias.php">
                                    <i class="fas fa-file-invoice"></i>
                                    Relatório de Pendências
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="lista_pacotes.php">
                                    <i class="fas fa-vial"></i>
                                    Gerenciar Amostras
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="cadastro_usuarios.php">
                                    <i class="fas fa-user-plus"></i>
                                    Cadastrar Usuário
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="listar.php">
                                    <i class="fas fa-users-cog"></i>
                                    Gerenciar Usuários
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="cadastrounidade.php">
                                    <i class="fas fa-map-marker-alt"></i>
                                    Cadastrar Locais
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="gerenciar_locais.php">
                                    <i class="fas fa-list"></i>
                                    Gerenciar Locais
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>
           
                <h1 class="text-center mb-4" style="color: #28a745;">Bem-vindo ao Sistema de Gerenciamento de Pacotes <?php echo ucfirst($user_name); ?></h1>
                <h2 class="text-center mb-4" style="color: #28a745;">Você está logado como um Usuário: <?php echo ucfirst($user_type); ?></h2>

                <h2 class="text-center mb-4" style="color: #28a745;">Pacotes no Local</h2>
                <div class="table-wrapper">
                    <table class="table table-bordered table-hover table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>Codigo de Barras</th>
                                <th>Status</th>
                                <th>Descrição</th>
                                <th>Data de Cadastro</th>
                                <th>Data de Envio</th>
                                <th>Data de Recebimento</th>
                                <th>Local de Cadastro</th>
                                <th>Local de Envio</th>
                                <th>Cadastrado por</th>
                                <th>Enviado por</th>
                                <th>Recebido por</th>
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
                                        <td><?php echo htmlspecialchars($pacote['data_cadastro']); ?></td>
                                        <td><?php echo htmlspecialchars($pacote['data_envio']); ?></td>
                                        <td><?php echo htmlspecialchars($pacote['data_recebimento']); ?></td>
                                        <td><?php echo htmlspecialchars($pacote['cadastro_nome']); ?></td>
                                        <td><?php echo htmlspecialchars($pacote['envio_nome']); ?></td>
                                        <td><?php echo htmlspecialchars($pacote['cadastrado_por']); ?></td>
                                        <td><?php echo htmlspecialchars($pacote['enviado_por']); ?></td>
                                        <td><?php echo htmlspecialchars($pacote['recebido_por']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="11">Nenhum pacote encontrado.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

        </div>

    </div>

    
    <div class="fixed-bottom toggle-footer cursor_to_down" id="footer_fixed">
            <!-- style="margin-top:50px;" -->
            <div class="fixed-bottom border-top bg-light text-center footer-content p-2" style="z-index:4; ">
                <!-- w3-card  -->
                <div class="footer-text">
                    Desenvolvido com &#128151; por Gerencia de Informatica - GETIN 
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
