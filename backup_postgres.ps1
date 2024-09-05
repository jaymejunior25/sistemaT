while ($true) {
    # Diretório onde os backups serão armazenados
    $backupDir = "C:\xampp\htdocs\sistema\backups"

    # Nome do banco de dados
    $dbName = "SBSENVIO"

    # Nome do usuário do PostgreSQL
    $dbUser = "postgres"

    # Senha do PostgreSQL
    $dbPassword = "admin"

    # Data atual
    $date = (Get-Date).ToString("yyyy-MM-dd")

    # Nome do arquivo de backup
    $backupFile = "$backupDir$dbName-$date.backup"

    # Comando de backup
    $pgDumpPath = "C:\Program Files\PostgreSQL\14\bin\pg_dump.exe"
    & $pgDumpPath --username=$dbUser --file=$backupFile --format=custom $dbName

    # Remover backups antigos (mais de 7 dias)
    Get-ChildItem -Path $backupDir -File | Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-7) } | Remove-Item

    # Esperar 24 horas antes de repetir o backup
    Start-Sleep -Seconds 86400
}
