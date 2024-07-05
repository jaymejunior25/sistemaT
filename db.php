<?php
$host = "Localhost";
$port = "5432";
$dbname = "SBSENVIO";
$user = "postgres";
$password = "admin";

try {
    $dbconn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $dbconn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
    die();
}  
/*$connectionString = "host=$host port=$port dbname=$dbname user=$user password=$password";
$dbconn = pg_connect($connectionString);
// Verifica se a conexão foi bem-sucedida
if (!$dbconn) {
    die("Erro: Não foi possível conectar ao banco de dados.");
}*/

