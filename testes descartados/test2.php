<?php
session_start();
include 'db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
if ($_SESSION['user_type'] != 'admin') {
    header('Location: index.php');
    exit();
}

// Verificar se há uma solicitação para excluir um pacote
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirmacao_exclusao'])) {
    $id_pacote = $_POST['id_pacote'];
    $senha_usuario = $_POST['senha_usuario'];

    // Verificar se a senha do usuário está correta
    $stmt = $dbconn->prepare("SELECT senha FROM usuarios WHERE id = :id_usuario");
    $stmt->execute([':id_usuario' => $_SESSION['user_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha_usuario, $usuario['senha'])) {
        // Senha correta, então excluir o pacote
        $stmt_delete = $dbconn->prepare("DELETE FROM pacotes WHERE id = :id_pacote");
        $stmt_delete->execute([':id_pacote' => $id_pacote]);

        // Redirecionar de volta para a mesma página após exclusão
        header('Location: listar_pacotes.php');
        exit();
    } else {
        // Senha incorreta, mostrar mensagem de erro
        $error_message = 'Senha incorreta. Não foi possível excluir o pacote.';
    }
}

// Consulta SQL para listar pacotes
$sql = "SELECT p.id, p.status, p.codigobarras, p.descricao, p.data_envio, p.data_recebimento, p.data_cadastro, l_envio.nome AS envio_nome, u_envio.usuario AS enviado_por, u_recebimento.usuario AS recebido_por,
        u_cadastro.usuario AS cadastrado_por, l_cadastro.nome AS cadastro_nome 
        FROM pacotes p 
        LEFT JOIN unidadehemopa l_envio ON p.unidade_envio_id = l_envio.id 
        LEFT JOIN unidadehemopa l_cadastro ON p.unidade_cadastro_id = l_cadastro.id 
        LEFT JOIN usuarios u_cadastro ON p.usuario_cadastro_id = u_cadastro.id 
        LEFT JOIN usuarios u_envio ON p.usuario_envio_id = u_envio.id 
        LEFT JOIN usuarios u_recebimento ON p.usuario_recebimento_id = u_recebimento.id
        ORDER BY p.data_cadastro DESC";

$stmt = $dbconn->prepare($sql);
$stmt->execute();
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
</head>
<body>
    <div class="container container-customlistas">
        <h1 class="text-center mb-4" style="color: #28a745;">Listar Pacotes</h1>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Status</th>
                        <th>Código de Barras</th>
                        <th>Descrição</th>
                        <th>Data de Cadastro</th>
                        <th>Data de Envio</th>
                        <th>Data de Recebimento</th>
                        <th>Unidade de Cadastro</th>
                        <th>Usuário que Cadastrou</th>
                        <th>Usuário que Enviou</th>
                        <th>Usuário que Recebeu</th>
                        <th>Unidade de Envio</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pacotes as $pacote): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($pacote['id']); ?></td>
                            <td><?php echo htmlspecialchars($pacote['status']); ?></td>
                            <td><?php echo htmlspecialchars($pacote['codigobarras']); ?></td>
                            <td><?php echo htmlspecialchars($pacote['descricao']); ?></td>
                            <td><?php echo htmlspecialchars($pacote['data_cadastro']); ?></td>
                            <td><?php echo htmlspecialchars($pacote['data_envio']); ?></td>
                            <td><?php echo htmlspecialchars($pacote['data_recebimento']); ?></td>
                            <td><?php echo htmlspecialchars($pacote['cadastro_nome']); ?></td>
                            <td><?php echo htmlspecialchars($pacote['cadastrado_por']); ?></td>
                            <td><?php echo htmlspecialchars($pacote['enviado_por']); ?></td>
                            <td><?php echo htmlspecialchars($pacote['recebido_por']); ?></td>
                            <td><?php echo htmlspecialchars($pacote['envio_nome']); ?></td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#confirmacaoModal-<?php echo $pacote['id']; ?>">
                                    Excluir
                                </button>
                                <!-- Modal de Confirmação -->
                                <div class="modal fade" id="confirmacaoModal-<?php echo $pacote['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="confirmacaoModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="confirmacaoModalLabel">Confirmação de Exclusão</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Para confirmar a exclusão do pacote, digite sua senha:</p>
                                                <form method="POST" action="">
                                                    <input type="hidden" name="id_pacote" value="<?php echo $pacote['id']; ?>">
                                                    <div class="form-group">
                                                        <label for="senha_usuario">Senha:</label>
                                                        <input type="password" class="form-control" id="senha_usuario" name="senha_usuario" required>
                                                    </div>
                                                    <button type="submit" name="confirmacao_exclusao" class="btn btn-danger">Confirmar Exclusão</button>
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Fim do Modal -->
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
