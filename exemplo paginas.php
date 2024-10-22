<?php
// Exemplo de query com paginação
$limite = 100; // Número de resultados por página
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina - 1) * $limite;

// Query ajustada com LIMIT e OFFSET
$query = "SELECT * FROM pacotes WHERE 1=1"; // Adicione seus filtros aqui

// Adicionar limites para a paginação
$query .= " LIMIT $limite OFFSET $offset";
$resultados = $db->query($query);
$pacotes = $resultados->fetchAll(PDO::FETCH_ASSOC);

// Número total de pacotes (para criar a paginação)
$total_query = "SELECT COUNT(*) FROM pacotes WHERE 1=1"; // Adicione seus filtros aqui
$total_pacotes = $db->query($total_query)->fetchColumn();
$total_paginas = ceil($total_pacotes / $limite);











// Adicionar a consulta para contar o número total de pacotes com os filtros aplicados
$sql_count = 'SELECT COUNT(p.id) AS total FROM pacotes p 
    LEFT JOIN unidadehemopa l_envio ON p.unidade_envio_id = l_envio.id 
    LEFT JOIN unidadehemopa l_cadastro ON p.unidade_cadastro_id = l_cadastro.id 
    LEFT JOIN usuarios u_cadastro ON p.usuario_cadastro_id = u_cadastro.id 
    LEFT JOIN usuarios u_envio ON p.usuario_envio_id = u_envio.id 
    LEFT JOIN usuarios u_recebimento ON p.usuario_recebimento_id = u_recebimento.id
    LEFT JOIN usuarios u_recebimentoLab ON p.usuario_recebimentolab_id = u_recebimentoLab.id
    LEFT JOIN laboratorio l_lab ON p.lab_id = l_lab.id';

$conditions_count = $conditions;  // Reutilize as condições da consulta anterior

if (count($conditions_count) > 0) {
    $sql_count .= " WHERE " . implode(" AND ", $conditions_count);
}

// Preparar e executar a consulta de contagem
$stmt_count = $dbconn->prepare($sql_count);
$stmt_count->execute($params);
$total_pacotes = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];

// Agora você tem o número total de pacotes, pode calcular o número de páginas
$total_paginas = ceil($total_pacotes / $limite);


?>
<!DOCTYPE html>
<html lang="en">
<head></head>
<body>
<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="theadfixed">
            <tr>
                <?php foreach ($colunas_selecionadas as $coluna): ?>
                    <th><?php echo $available_columns[$coluna]; ?></th>
                <?php endforeach; ?>
                <?php if ($_SESSION['user_type'] === 'admin'): ?><th>Ações</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pacotes as $pacote): ?>
                <tr>
                    <?php foreach ($colunas_selecionadas as $coluna): ?>
                        <td>
                            <?php
                            if ($coluna == 'data_cadastro' || $coluna == 'data_envio' || $coluna == 'data_recebimento' || $coluna == 'data_recebimentolab') {
                                $data = $pacote[$coluna];
                                if ($data) {
                                    $dateTime = new DateTime($data);
                                    echo $dateTime->format('d-m-Y H:i');
                                }
                            } elseif ($coluna == 'status') {
                                if ($pacote['status'] == 'cadastrado') {
                                    echo '<span class="badge badge-danger">cadastrado</span>';
                                } elseif ($pacote['status'] == 'enviado') {
                                    echo '<span class="badge badge-warning">enviado</span>';
                                } elseif ($pacote['status'] == 'recebido') {
                                    echo '<span class="badge badge-success">recebido</span>';
                                } elseif ($pacote['status'] == 'recebidolab') {
                                    echo '<span class="badge badge-primary">recebido LAB</span>';
                                }
                            } else {
                                echo htmlspecialchars($pacote[$coluna]);
                            }
                            ?>
                        </td>
                    <?php endforeach; ?>
                    <?php if ($_SESSION['user_type'] === 'admin'): ?>
                        <td class="btn-group-vertical">
                            <a href="editar_pacote.php?id=<?php echo $pacote['id']; ?>" class="btn btn-primary btn-sm">Editar</a>
                            <button type="button"  class="btn btn-danger btn-sm" onclick="openDeleteModal(<?php echo $pacote['id']; ?>)">Excluir</button>
                        </td><?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Paginação -->
<nav aria-label="Navegação de página">
    <ul class="pagination justify-content-center">
        <!-- Link para a primeira página -->
        <li class="page-item <?php if($pagina_atual <= 1){ echo 'disabled'; } ?>">
            <a class="page-link" href="?pagina=1">Primeira</a>
        </li>

        <!-- Link para a página anterior -->
        <li class="page-item <?php if($pagina_atual <= 1){ echo 'disabled'; } ?>">
            <a class="page-link" href="?pagina=<?php echo $pagina_atual - 1; ?>">Anterior</a>
        </li>

        <!-- Páginas numeradas -->
        <?php for($i = 1; $i <= $total_paginas; $i++): ?>
            <li class="page-item <?php if($pagina_atual == $i){ echo 'active'; } ?>">
                <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>

        <!-- Link para a próxima página -->
        <li class="page-item <?php if($pagina_atual >= $total_paginas){ echo 'disabled'; } ?>">
            <a class="page-link" href="?pagina=<?php echo $pagina_atual + 1; ?>">Próxima</a>
        </li>

        <!-- Link para a última página -->
        <li class="page-item <?php if($pagina_atual >= $total_paginas){ echo 'disabled'; } ?>">
            <a class="page-link" href="?pagina=<?php echo $total_paginas; ?>">Última</a>
        </li>
    </ul>
</nav>











<!-- Paginação -->
<nav aria-label="Navegação de página">
    <ul class="pagination justify-content-center">
        <!-- Link para a primeira página -->
        <li class="page-item <?php if($pagina_atual <= 1){ echo 'disabled'; } ?>">
            <a class="page-link" href="?pagina=1">Primeira</a>
        </li>

        <!-- Link para a página anterior -->
        <li class="page-item <?php if($pagina_atual <= 1){ echo 'disabled'; } ?>">
            <a class="page-link" href="?pagina=<?php echo $pagina_atual - 1; ?>">Anterior</a>
        </li>

        <!-- Páginas numeradas -->
        <?php
        // Definir o intervalo de páginas a serem mostradas
        $inicio = max(1, $pagina_atual - 5);  // Mostra 5 páginas antes da atual
        $fim = min($total_paginas, $pagina_atual + 4);  // Mostra 4 páginas depois da atual

        // Ajusta para sempre exibir no máximo 10 páginas
        if (($fim - $inicio) < 9) {
            $inicio = max(1, $fim - 9);
        }

        for($i = $inicio; $i <= $fim; $i++): ?>
            <li class="page-item <?php if($pagina_atual == $i){ echo 'active'; } ?>">
                <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>

        <!-- Link para a próxima página -->
        <li class="page-item <?php if($pagina_atual >= $total_paginas){ echo 'disabled'; } ?>">
            <a class="page-link" href="?pagina=<?php echo $pagina_atual + 1; ?>">Próxima</a>
        </li>

        <!-- Link para a última página -->
        <li class="page-item <?php if($pagina_atual >= $total_paginas){ echo 'disabled'; } ?>">
            <a class="page-link" href="?pagina=<?php echo $total_paginas; ?>">Última</a>
        </li>
    </ul>
</nav>



</body>


