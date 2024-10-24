<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['user_type'] != 'admin') {
    header('Location: index.php');
    exit();
}

$usuario_id = $_GET['id'];

// Obter o usuário atual e os locais vinculados
$stmt = $dbconn->prepare("SELECT * FROM usuarios WHERE id = :usuario_id");
$stmt->execute(['usuario_id' => $usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    $_SESSION['error_message'] = 'Usuário não encontrado.';
    header('Location: gerenciar_usuarios.php');
    exit;
}
$stmt = $dbconn->prepare("SELECT local_id FROM usuario_local WHERE usuario_id = :usuario_id");
$stmt->execute(['usuario_id' => $usuario_id]);
$locais_vinculados = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

// Obter a lista de locais para exibir no formulário
$stmt = $dbconn->prepare("SELECT id, nome FROM unidadehemopa");
$stmt->execute();
$locais = $stmt->fetchAll(PDO::FETCH_ASSOC);


$stmt = $dbconn->prepare("SELECT laboratorio_id FROM usuario_laboratorio WHERE usuario_id = :usuario_id");
$stmt->execute(['usuario_id' => $usuario_id]);
$laboratorio_vinculado = $stmt->fetch(PDO::FETCH_COLUMN);

// Obter a lista de laboratórios únicos para exibir no formulário
$stmt = $dbconn->prepare("SELECT DISTINCT nome FROM laboratorio");
$stmt->execute();
$laboratorios = $stmt->fetchAll(PDO::FETCH_ASSOC);


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $senha_confirmacao = $_POST['senha_confirmacao'];
    $user_id = $_SESSION['user_id'];

    // Verificar a senha do administrador
    $stmt = $dbconn->prepare("SELECT senha FROM usuarios WHERE id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (password_verify($senha_confirmacao, $admin['senha'])) {
        // Processar o formulário de atualização de usuário
        $nome = $_POST['nome'];
        $matricula = $_POST['matricula'];
        $usuario = $_POST['usuario'];
        $tipo = $_POST['tipoconta'];
        $locais_selecionados = $_POST['unidades_hemopa'];
        $laboratorio_selecionado = $_POST['laboratorio'];

        // Atualizar o usuário no banco de dados
        $stmt = $dbconn->prepare("UPDATE usuarios SET nome = :nome, matricula = :matricula, tipoconta = :tipoconta, usuario = :usuario WHERE id = :usuario_id");
        $stmt->execute(['nome' => $nome, 'matricula' => $matricula, 'tipoconta' => $tipo, 'usuario' => $usuario, 'usuario_id' => $usuario_id]);
        if (!empty($_POST['senha']) && !empty($_POST['confirmar_senha'])) {
            $senha = $_POST['senha'];
            $confirmar_senha = $_POST['confirmar_senha'];
        
            if (password_verify($senha_confirmacao, $admin['senha'])) {
                // Validar senha
                if (strlen($_POST['senha']) < 6 || !preg_match('/[A-Za-z]/', $_POST['senha']) || !preg_match('/\d/', $_POST['senha'])) {
                    $_SESSION['error_message'] = 'A senha deve ter pelo menos 6 caracteres e incluir números e letras.';
                } elseif ($_POST['senha'] !== $confirmar_senha) {
                    $_SESSION['error_message'] = 'As senhas não correspondem.';
                } else {// Hash da nova senha antes de salvar no banco de dados
                    $senha_hash = password_hash($senha, PASSWORD_BCRYPT);
                
                    // Atualizar a senha no banco de dados
                    $stmt = $dbconn->prepare("UPDATE usuarios SET senha = :senha WHERE id = :usuario_id");
                    $stmt->execute(['senha' => $senha_hash, 'usuario_id' => $usuario_id]);
                }
            }
        }

        // Remover locais existentes
        $stmt = $dbconn->prepare("DELETE FROM usuario_local WHERE usuario_id = :usuario_id");
        $stmt->execute(['usuario_id' => $usuario_id]);

        // Remover o laboratório existente
        $stmt = $dbconn->prepare("DELETE FROM usuario_laboratorio WHERE usuario_id = :usuario_id");
        $stmt->execute(['usuario_id' => $usuario_id]);
        // Buscar o ID do laboratório selecionado pelo nome
        $stmt = $dbconn->prepare("SELECT id FROM laboratorio WHERE nome = :nome LIMIT 1");
        $stmt->execute(['nome' => $laboratorio_selecionado]);
        $laboratorio_id = $stmt->fetchColumn();

        // Inserir o novo laboratório vinculado
        if ($laboratorio_id) {
            $stmt = $dbconn->prepare("INSERT INTO usuario_laboratorio (usuario_id, laboratorio_id) VALUES (:usuario_id, :laboratorio_id)");
            $stmt->execute(['usuario_id' => $usuario_id, 'laboratorio_id' => $laboratorio_id]);
        }
        // Inserir novos locais
        foreach ($locais_selecionados as $local_id) {
            $stmt = $dbconn->prepare("INSERT INTO usuario_local (usuario_id, local_id) VALUES (:usuario_id, :local_id)");
            $stmt->execute(['usuario_id' => $usuario_id, 'local_id' => $local_id]);
            if ($local_id == 1){
                $stmt = $dbconn->prepare("UPDATE usuarios SET unidade_id = :unidade WHERE id = :usuario_id");
                $stmt->execute(['unidade' => $local_id, 'usuario_id' => $usuario_id]);

            }
        }
        // Definir a mensagem de sucesso e redirecionar para listar.php
        $_SESSION['success_message'] = 'Usuário atualizado com sucesso!';
        header('Location: listar.php');
    } else {
        $_SESSION['error_message'] = 'Senha do usuário atual incorreta. Tente novamente.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário</title>
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container container-customlistas">
        <h1 class="text-center mb-4" style="color: #28a745;">Editar Usuário</h1>
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="" id="editarForm">
            <div class="form-group">
                <label for="nome" style="color: #28a745;">Nome:</label>
                <input type="text" name="nome" id="nome" class="form-control" value="<?php echo $usuario['nome']; ?>" required>
            </div>
            <div class="form-group">
                <label for="matricula" style="color: #28a745">Matrícula:</label>
                <input type="text" id="matricula" name="matricula" class="form-control" value="<?php echo $usuario['matricula']; ?>" required>
            </div>
            <div class="form-group">
                <label for="usuario" style="color: #28a745">Usuário:</label>
                <input type="text" id="usuario" name="usuario" class="form-control" value="<?php echo $usuario['usuario']; ?>" required>
            </div>
            <div class="form-group">
                <label for="senha" style="color: #28a745;">Nova Senha:</label>
                <div class="input-group">
                <input type="password" name="senha" id="senha" class="form-control" placeholder="Digite a nova senha">
                <div class="input-group-append">
                        <button type="button" class="btn btn-outline-secondary" id="gerarSenha">Gerar Senha</button>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="confirmar_senha" style="color: #28a745;">Confirmar Senha:</label>
                <div class="input-group">
                <input type="password" name="confirmar_senha" id="confirmar_senha" class="form-control" placeholder="Confirme a nova senha">
                <div class="input-group-append">
                        <button type="button" class="btn btn-outline-secondary" id="gerarSenha">Gerar Senha</button>
                </div>
                </div>
            </div>
            <div class="form-group">
                <label for="tipoconta" style="color: #28a745;">Função:</label>
                <select name="tipoconta" id="tipoconta" class="form-control" required>
                    <option value="normal" <?php echo $usuario['tipoconta'] == 'normal' ? 'selected' : ''; ?>>Normal</option>
                    <option value="admin" <?php echo $usuario['tipoconta'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label for="unidades_hemopa" style="color: #28a745">Locais:</label>
                <div>
                    <?php foreach ($locais as $local): ?>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="unidades_hemopa[]" value="<?php echo $local['id']; ?>" <?php if (in_array($local['id'], $locais_vinculados)) echo 'checked'; ?>>
                            <label class="form-check-label" style="color: #28a745;"><?php echo $local['nome']; ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="form-group">
                <label for="laboratorio" style="color: #28a745;">Laboratório:</label>
                <select name="laboratorio" id="laboratorio" class="form-control" required>
                    <option value="">Sem laboratório</option> <!-- Adiciona a opção "Nenhum" -->
                    <?php foreach ($laboratorios as $laboratorio): ?>
                        <option value="<?php echo $laboratorio['nome']; ?>" <?php echo ($laboratorio_vinculado == $laboratorio['nome']) ? 'selected' : ''; ?>>
                            <?php echo $laboratorio['nome']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="button" class="btn btn-custom btn-block" data-toggle="modal" data-target="#confirmPasswordModal">
                <i class="fas fa-save"></i> Salvar
            </button>
        </form>
        <div class="text-center mt-3">
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-angle-left"></i> Voltar</a>
        </div>
        <a href="logout.php" class="btn btn-danger btn-lg mt-3"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Modal de Confirmação de Senha -->
    <div class="modal fade" id="confirmPasswordModal" tabindex="-1" role="dialog" aria-labelledby="confirmPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmPasswordModalLabel">Confirmação de Senha</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="senha_confirmacao">Por favor, insira sua senha para confirmar a atualização:</label>
                        <input type="password" class="form-control" id="senha_confirmacao" name="senha_confirmacao" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="confirmarEdicao">Confirmar</button>
                </div>
            </div>
        </div>
    </div>
    <div class="fixed-bottom toggle-footer cursor_to_down" id="footer_fixed" >
        <div class="fixed-bottom border-top bg-light text-center footer-content p-2" style="z-index:4;">
            <div class="footer-text">
                Desenvolvido com &#128151; por Gerencia de Informatica - GETIN <br>
                <a class="text-reset fw-bold" href="http://www.hemopa.pa.gov.br/site/">© Todos os direitos reservados 2024 Hemopa.</a>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.getElementById('confirmarEdicao').addEventListener('click', function () {
            // Adiciona o campo de confirmação de senha ao formulário
            var senhaConfirmacao = document.getElementById('senha_confirmacao').value;
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'senha_confirmacao';
            input.value = senhaConfirmacao;
            document.getElementById('editarForm').appendChild(input);
            // Submete o formulário
            document.getElementById('editarForm').submit();
        });
        document.getElementById('gerarSenha').addEventListener('click', function () {
            // Define a senha padrão
            const senhaPadrao = 'hemopa@2024';

            // Preenche os campos de senha e confirmar senha
            document.getElementById('senha').value = senhaPadrao;
            document.getElementById('confirmar_senha').value = senhaPadrao;
        });
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
