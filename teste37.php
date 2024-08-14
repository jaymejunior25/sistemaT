<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Obter a lista de laboratórios únicos para exibir no formulário
$stmt = $dbconn->prepare("SELECT DISTINCT nome FROM laboratorio");
$stmt->execute();
$laboratorios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$amostras = [];
$usuario_laboratorio = null;
$funcionario_info = null;
$error_message = '';
$success_message = '';

// Manter amostras e informações do funcionário entre requisições
if (isset($_SESSION['amostras'])) {
    $amostras = $_SESSION['amostras'];
}

// Variáveis para manter o estado dos campos
$laboratorio_selecionado = '';
$data_recebimento = date('Y-m-d');
$matricula = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['buscar_amostras'])) {
        $laboratorio = $_POST['laboratorio'];
        $laboratorio_selecionado = $_POST['laboratorio'];
        $data_recebimento = $_POST['data_recebimento'];
        
        // Formatar a data para o formato 'aaaa-mm-dd'
        $data_recebimento = date('Y-m-d', strtotime($data_recebimento));
        
        // Buscar o ID do laboratório com base no nome
        $stmt = $dbconn->prepare("SELECT id FROM laboratorio WHERE nome = :nome");
        $stmt->execute([':nome' => $laboratorio]);
        $laboratorio_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($laboratorio_ids)) {
            $error_message = 'Laboratório não encontrado.';
        } else {
            $laboratorio_id = $laboratorio_ids[0];  // Definindo $laboratorio_id para usar posteriormente

            // Buscar amostras do laboratório e data especificados
            $placeholders = implode(',', array_fill(0, count($laboratorio_ids), '?'));
            $stmt = $dbconn->prepare("
                SELECT p.*, 
                       l.nome AS lab_nome,
                       lc.nome AS local_cadastro_nome,
                       le.nome AS local_envio_nome,
                       uc.nome AS cadastro_nome,
                       ue.usuario AS envio_nome,
                       ur.usuario AS recebido_por
                FROM pacotes p
                LEFT JOIN laboratorio l ON p.lab_id = l.id
                LEFT JOIN unidadehemopa lc ON p.unidade_cadastro_id = lc.id
                LEFT JOIN unidadehemopa le ON p.unidade_envio_id = le.id
                LEFT JOIN usuarios uc ON p.usuario_cadastro_id = uc.id
                LEFT JOIN usuarios ue ON p.usuario_envio_id = ue.id
                LEFT JOIN usuarios ur ON p.usuario_recebimento_id = ur.id
                WHERE p.lab_id IN ($placeholders) 
                  AND DATE(p.data_recebimento) = ?
                  AND p.status = 'recebido'
            ");
            
            $params = array_merge($laboratorio_ids, [$data_recebimento]);
            $stmt->execute($params);
            $amostras = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Armazenar amostras na sessão
            $_SESSION['amostras'] = $amostras;
        }
    } elseif (isset($_POST['verificar_matricula'])) {
        $matricula = $_POST['matricula'];
        
        // Buscar informações do funcionário com base na matrícula
        $stmt = $dbconn->prepare("
            SELECT u.nome, u.usuario, l.nome AS lab_nome
            FROM usuarios u
            LEFT JOIN usuario_laboratorio ul ON u.id = ul.usuario_id
            LEFT JOIN laboratorio l ON ul.laboratorio_id = l.id
            WHERE u.matricula = :matricula
        ");
        $stmt->execute([':matricula' => $matricula]);
        $funcionario_info = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$funcionario_info) {
            $error_message = 'Matrícula não encontrada.';
        }
    } elseif (isset($_POST['confirmar_recebimento'])) {
        $matricula = $_POST['matricula'];
        $laboratorio = $_POST['laboratorio'];
        $laboratorio_selecionado = $_POST['laboratorio'];
        $data_recebimento = $_POST['data_recebimento'];

        // Formatar a data para o formato 'aaaa-mm-dd'
        $data_recebimento = date('Y-m-d', strtotime($data_recebimento));
        
        // Buscar o ID do laboratório com base no nome
        $stmt = $dbconn->prepare("SELECT id FROM laboratorio WHERE nome = :nome");
        $stmt->execute([':nome' => $laboratorio]);
        $laboratorio_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Verificar o laboratório vinculado ao usuário para a confirmação do recebimento
        $stmt = $dbconn->prepare("SELECT l.nome FROM usuario_laboratorio ul 
                                  JOIN laboratorio l ON ul.laboratorio_id = l.id 
                                  JOIN usuarios u ON ul.usuario_id = u.id 
                                  WHERE u.matricula = :matricula");
        $stmt->execute([':matricula' => $matricula]);
        $usuario_laboratorio = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario_laboratorio || $usuario_laboratorio['nome'] != $laboratorio) {
            $error_message = 'O laboratório selecionado não corresponde ao laboratório vinculado ao usuário.';
            $amostras = $_SESSION['amostras'] ?? []; // Mantém as amostras em caso de erro
        } else {
            if (empty($laboratorio_ids)) {
                $error_message = 'Laboratório não encontrado.';
            } else {
                $laboratorio_id = $laboratorio_ids[0];  // Definindo $laboratorio_id para usar posteriormente
    
                // Confirmar o recebimento das amostras
                $placeholders = implode(',', array_fill(0, count($laboratorio_ids), '?'));
                $stmt = $dbconn->prepare("
                    UPDATE pacotes 
                    SET status = 'recebidolab', usuario_recebimentoLab_id = ?, data_recebimentoLab = NOW()
                    WHERE lab_id IN ($placeholders) 
                      AND DATE(data_recebimento) = ? 
                      AND status = 'recebido'
                ");
    
                $params = array_merge([$_SESSION['user_id']], $laboratorio_ids, [$data_recebimento]);
                $stmt->execute($params);
    
                if ($stmt->rowCount() > 0) {
                    $success_message = 'Recebimento confirmado com sucesso!';
                    // Limpar amostras da sessão após confirmação bem-sucedida
                    unset($_SESSION['amostras']);
                } else {
                    $error_message = 'Nenhuma amostra encontrada ou já recebida.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recebimento de Amostras</title>
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('form').on('submit', function(event) {
                event.preventDefault(); // Evita o envio padrão do formulário

                var form = $(this);
                $.ajax({
                    type: form.attr('method'),
                    url: form.attr('action'),
                    data: form.serialize(),
                    success: function(response) {
                        // Atualiza a página com a resposta do servidor
                        $('.container').html(response);
                    },
                    error: function() {
                        alert('Erro ao processar a solicitação.');
                    }
                });
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <h1 class="text-center my-4">Recebimento de Amostras</h1>
        
        <form method="post" action="">
            <div class="form-group">
                <label for="laboratorio" style="color: #28a745;">Laboratório:</label>
                <select name="laboratorio" id="laboratorio" class="form-control" required>
                    <?php foreach ($laboratorios as $lab): ?>
                        <option value="<?php echo htmlspecialchars($lab['nome']); ?>" <?php if (isset($_POST['laboratorio']) && $_POST['laboratorio'] == $lab['nome']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($lab['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="data_recebimento">Data de Recebimento:</label>
                <input type="date" id="data_recebimento" name="data_recebimento" class="form-control" value="<?php echo isset($_POST['data_recebimento']) ? htmlspecialchars($_POST['data_recebimento']) : date('Y-m-d'); ?>" required>
            </div>
            <button type="submit" name="buscar_amostras" class="btn btn-primary btn-block">Buscar Amostras</button>
        </form>

        <?php if (!empty($error_message)): ?>
            <div class='alert alert-danger mt-4'><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if (!empty($amostras)): ?>
            <?php if (!empty($laboratorio)): ?>
            <h3 class="mt-4">Laboratório: <?php echo htmlspecialchars($usuario_laboratorio['nome'] ?? $laboratorio); ?></h3>
            <table class="table table-striped mt-4">
                <thead class="theadfixed">
                    <tr>
                        <th>Cod. Barras</th>
                        <th>Status</th>
                        <th>Descrição</th>
                        <th>Laboratório</th>
                        <th>Data Cadastro</th>
                        <th>Data Envio</th>
                        <th>Data Recebimento</th>
                        <th>Local de Envio</th>
                        <th>Envio por</th>
                        <th>Recebido por</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($amostras as $amostra): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($amostra['codigobarras']); ?></td>
                            <td><?php echo htmlspecialchars($amostra['status']); ?></td>
                            <td><?php echo htmlspecialchars($amostra['descricao']); ?></td>
                            <td><?php echo htmlspecialchars($amostra['lab_nome']); ?></td>
                            <td><?php echo htmlspecialchars($amostra['data_cadastro']); ?></td>
                            <td><?php echo htmlspecialchars($amostra['data_envio']); ?></td>
                            <td><?php echo htmlspecialchars($amostra['data_recebimento']); ?></td>
                            <td><?php echo htmlspecialchars($amostra['local_envio_nome']); ?></td>
                            <td><?php echo htmlspecialchars($amostra['envio_nome']); ?></td>
                            <td><?php echo htmlspecialchars($amostra['recebido_por']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="text-right">
                <strong>Total de Amostras:</strong> <?php echo count($amostras); ?>
            </div>
        <?php endif; ?>
        <?php endif; ?>
        <hr>

        <h3 class="mt-4">Verificar Matrícula</h3>
        <form method="post" action="">
            <div class="form-group">
                <label for="matricula">Matrícula do Funcionário:</label>
                <input type="text" id="matricula" name="matricula" class="form-control" value="<?php echo isset($_POST['matricula']) ? htmlspecialchars($_POST['matricula']) : ''; ?>" required>
            </div>
            <button type="submit" name="verificar_matricula" class="btn btn-success btn-block">Verificar</button>
        </form>

        <?php if ($funcionario_info): ?>
            <div class="mt-4">
                <h5>Informações do Funcionário:</h5>
                <ul>
                    <li><strong>Nome:</strong> <?php echo htmlspecialchars($funcionario_info['nome']); ?></li>
                    <li><strong>Login:</strong> <?php echo htmlspecialchars($funcionario_info['usuario']); ?></li>
                    <li><strong>Laboratório:</strong> <?php echo htmlspecialchars($funcionario_info['lab_nome']); ?></li>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <input type="hidden" name="laboratorio" value="<?php echo htmlspecialchars($_POST['laboratorio'] ?? ''); ?>">
            <input type="hidden" name="data_recebimento" value="<?php echo htmlspecialchars($_POST['data_recebimento'] ?? ''); ?>">
            <input type="hidden" name="matricula" value="<?php echo htmlspecialchars($_POST['matricula'] ?? ''); ?>">
            <button type="submit" name="confirmar_recebimento" class="btn btn-danger btn-block mt-4">Confirmar Recebimento</button>
        </form>

        <?php if (!empty($success_message)): ?>
            <div class='alert alert-success mt-4'><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <div class="text-center mt-3">
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-angle-left"></i> Voltar</a>
        </div>
        <a href="logout.php" class="btn btn-danger btn-lg mt-3">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
    <div class="fixed-bottom toggle-footer cursor_to_down" id="footer_fixed">
        <div class="fixed-bottom border-top bg-light text-center footer-content p-2" style="z-index:4;">
            <div class="footer-text">
                Desenvolvido com &#128151; por Gerencia de Informatica - GETIN <br>
                <a class="text-reset fw-bold" href="http://www.hemopa.pa.gov.br/site/">© Todos os direitos reservados 2024 Hemopa.</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>