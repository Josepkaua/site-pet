#!/bin/bash
set -e

echo "=== Clínica Vet – Setup de banco de dados ==="
php /var/www/html/api/setup_db.php

echo "=== Iniciando Apache ==="
exec apache2-foreground
