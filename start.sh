#!/bin/bash
set -e

echo "=== Clínica Vet – Setup de banco de dados ==="
# Falha no setup do banco não deve impedir o site de subir
php /var/www/html/api/setup_db.php || echo "[WARN] Setup do banco falhou – o site seguirá com dados de fallback."

echo "=== Iniciando Apache ==="
exec apache2-foreground
