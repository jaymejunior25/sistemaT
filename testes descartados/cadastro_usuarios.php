<?php
session_start();
include 'db.php';

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Obter a lista de locais para exibir no formulário
$stmt = $dbconn->prepare("SELECT id, nome FROM unidadehemopa");
$stmt->execute();
$locais = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    /*$username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $tipo = $_POST['tipo'];
    $local_id = $_POST['local_id'];*/

    $nome = $_POST['nome'];
    $matricula = $_POST['matricula'];
    $usuario = $_POST['usuario'];
    $senha = password_hash($_POST['senha'], PASSWORD_BCRYPT);
    $unidade = $_POST['unidade'];
    $tipo = $_POST['tipoconta'];
    $local_id = $_POST['unidadehemopa_id'];

    // Inserir o novo usuário no banco de dados
    $stmt = $dbconn->prepare("INSERT INTO usuarios (nome, senha, matricula, tipoconta, unidade, unidade_id, usuario) VALUES (:nome, :senha, :matricula, :tipoconta, :unidade, :unidadehemopa_id, :usuario)");
    $stmt->execute(['nome' => $nome, 'senha' => $senha, 'matricula' =>$matricula, 'tipoconta' => $tipo, 'unidade' => $unidade, 'unidadehemopa_id' => $local_id, 'usuario' => $usuario]);

    echo 'Usuário cadastrado com sucesso!';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cadastrar Usuário</title>
</head>
<body>
    <div class="container">
        <h1>Cadastro de Usuários</h1>
        <form action="" method="POST">
            <div class="form-group">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" required>
            </div>
            <div class="form-group">
                <label for="matricula">matricula:</label>
                <input type="text" id="matricula" name="matricula" required>
            </div>
            <div class="form-group">
                <label for="usuario">Usuario:</label>
                <input type="text" id="usuario" name="usuario" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            <div class="form-group">
                <label for="unidade">Unidade:</label>
                <input type="text" id="unidade" name="unidade" required>
            </div>
            <div class="form-group">
                <label for="tipoconta">Tipo de Usuário:</label>
                <select name="tipoconta" id="tipoconta" required>
                    <option value='admin'>Administrador</option>
                    <option value='normal'>Usuário Normal</option>
                </select><br><br>
            </div>
            <div class="form-group">
                <label for="unidadehemopa_id">Local:</label>
                <select name="unidadehemopa_id" id="unidadehemopa_id" required>
                    <?php foreach ($locais as $local): ?>
                        <option value="<?php echo $local['id']; ?>"><?php echo $local['nome']; ?></option>
                    <?php endforeach; ?>
                </select><br><br>
            </div>
            <div class="form-group">
                <button type="submit">Cadastrar</button>
            </div>
        </form>
    </div>
    <a href="index.php">Voltar</a>
</body>
</html>

