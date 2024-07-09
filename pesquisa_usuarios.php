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

$searchType = $_GET['searchType'];
$searchQuery = $_GET['searchQuery'];
$queryParam = '%' . $searchQuery . '%';

$sql = "SELECT u.*, uh.nome AS unidade_nome 
        FROM usuarios u
        LEFT JOIN unidadehemopa uh ON u.unidade_id = uh.id";

switch ($searchType) {
    case 'nome':
        $sql .= " WHERE u.nome LIKE :query";
        break;
    case 'usuario':
        $sql .= " WHERE u.usuario LIKE :query";
        break;
    case 'matricula':
        $sql .= " WHERE u.matricula LIKE :query";
        break;
    case 'unidade':
        $sql .= " WHERE uh.nome LIKE :query";
        break;
    default:
        $sql .= " WHERE 1";
        break;
}

$stmt = $dbconn->prepare($sql);
$stmt->bindParam(':query', $queryParam);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados da Pesquisa</title>
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container container-customlistas">
        <h1 class="text-center mb-4" style="color: #28a745;">Resultados da Pesquisa</h1>
        <div class="table-wrapper">
            <table class="table table-bordered table-hover table-striped">
                <thead class="theadfixed">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Matricula</th>
                        <th>Usuario</th>
                        <th>Unidade</th>
                        <th>Tipo Conta</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($usuarios) > 0): ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usuario['id']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['matricula']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['usuario']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['unidade_nome']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['tipoconta']); ?></td>
                                <td>
                                    <a href="editar.php?id=<?php echo $usuario['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <a href="excluir.php?id=<?php echo $usuario['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este usuário?');">
                                        <i class="fas fa-trash-alt"></i> Excluir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">Nenhum usuário encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="text-center mt-3">
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-angle-left"></i>Voltar</a>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-home"></i> Home</a>
            <a href="#" class="btn btn-custom" data-toggle="modal" data-target="#searchModal">
                <i class="fas fa-search"></i> Pesquisar Usuários
            </a>
        </div>
        <a href="logout.php" class="btn btn-danger btn-lg mt-3">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
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
    <!-- Modal de Pesquisa -->
    <div class="modal fade" id="searchModal" tabindex="-1" role="dialog" aria-labelledby="searchModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="searchModalLabel">Pesquisar Usuários</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="searchForm" method="GET" action="pesquisa_usuarios.php">
                        <div class="form-group">
                            <label for="searchType">Pesquisar por:</label>
                            <select class="form-control" id="searchType" name="searchType" required>
                                <option value="nome">Nome</option>
                                <option value="usuario">Usuário</option>
                                <option value="matricula">Matrícula</option>
                                <option value="unidade">Local de Cadastro</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="searchQuery">Pesquisa:</label>
                            <input type="text" class="form-control" id="searchQuery" name="searchQuery" required>
                        </div>
                        <button type="submit" class="btn btn-custom"><i class="fas fa-search"></i>Pesquisar</button>
                    </form>
                </div>
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
