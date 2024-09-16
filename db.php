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

// Configurações de conexão com o banco de dados
$host1 = '10.95.2.31'; // endereço do servidor PostgreSQL
$dbname1 = 'sbs_prod'; // nome do banco de dados
$port1 = "5432";
$user1 = 'sbsadmin'; // usuário do banco de dados
$password1 = 'sbs2011'; // senha do banco de dados

try {
    $dbconn1 = new PDO("pgsql:host=$host1;port=$port1;dbname=$dbname1", $user1, $password1);
    $dbconn1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
   // echo "Erro: " . $e->getMessage();
    //die();
    error_log("Erro na conexão: " . $e->getMessage(), 3, 'C:\xampp\php\logs\php_error.log');
    die(json_encode(['error' => 'Erro na conexão: ' . $e->getMessage()]));
}  