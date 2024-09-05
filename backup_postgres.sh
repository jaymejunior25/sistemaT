#!/bin/bash

# Configurações do Banco de Dados
DB_HOST="Localhost"
DB_PORT="5432"
DB_USER="postgres"
DB_NAME="SBSENVIO"

# Configurações de Backup
BACKUP_DIR="C:\xampp\htdocs\sistema\backups"
DATE=$(date +"%Y-%m-%d")
BACKUP_FILE="$BACKUP_DIR/${DB_NAME}_$DATE.sql"

# Comando de Backup
PGPASSWORD="admin" pg_dump -h $DB_HOST -p $DB_PORT -U $DB_USER -F c -b -v -f $BACKUP_FILE $DB_NAME

# Remover backups mais antigos que 7 dias
find $BACKUP_DIR -type f -name "*.sql" -mtime +7 -exec rm {} \;
