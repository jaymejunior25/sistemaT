<?php
session_start();
include 'db.php';

// Mapeamento do código de local
$local_id = $_SESSION['unidade_nome'];
$codigolocal_map = [
    'Castanheira' => 4,
    'Coleta Externa' => 5,
    'Metropole' => 6
];

// Verificação do local
if (!isset($codigolocal_map[$local_id])) {
    echo "<div class='alert alert-danger'>Local não reconhecido.</div>";
    exit;
}

$codigolocal = $codigolocal_map[$local_id];

try {

    // Consulta ao banco de dados
    $sql = "SELECT cdamostra FROM coleta WHERE dtcoleta = current_date AND hrtermcoleta IS NOT NULL AND cdentjurloccoleta = :codigolocal";
    $stmt = $dbconn1->prepare($sql);
    $stmt->execute([':codigolocal' => $codigolocal]);

    $amostras = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Verifica se há amostras
    if (count($amostras) > 0) {
        // Exibir tabela
        echo "<div class='container mt-4'>";
        echo "<h3 class='mb-3'>Amostras da data atual - Local: " . htmlspecialchars($local_id) . "</h3>";
        echo "<table class='table table-striped'>";
        echo "<thead><tr><th>#</th><th>Código da Amostra</th></tr></thead>";
        echo "<tbody>";

        $index = 1;
        foreach ($amostras as $amostra) {
            echo "<tr><td>{$index}</td><td>{$amostra['cdamostra']}</td></tr>";
            $index++;
        }

        echo "</tbody></table>";
        echo "</div>";
    } else {
        echo "<div class='alert alert-info mt-4'>Nenhuma amostra encontrada para o local {$local_id} na data atual.</div>";
    }
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Erro: " . $e->getMessage() . "</div>";
}
?>
