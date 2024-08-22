<?php
// Configurações do banco de dados
$host = "Localhost";
$port = "5432";
$dbname = "SBSENVIO";
$user = "postgres";
$password = "admin";

// Diretório onde os backups serão armazenados
$backupDir = 'C:\xampp\htdocs\sistema\backup do sistema';

// Nome do arquivo de backup com data
$backupFile = $backupDir . 'backup_' . date('Y-m-d') . '.sql';

// Comando para executar o pg_dump
$command = "pg_dump --host=$host --dbname=$dbname --username=$user --file=$backupFile";

// Executar o comando de backup
system($command, $return_var);

if ($return_var !== 0) {
    echo "Erro ao criar o backup.";
} else {
    echo "Backup criado com sucesso: $backupFile";
}

// Excluir backups antigos (mais de 7 dias)
$files = glob($backupDir . 'backup_*.sql');

foreach ($files as $file) {
    if (filemtime($file) < time() - 7 * 24 * 60 * 60) { // 7 dias
        unlink($file);
    }
}
