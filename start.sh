#!/bin/bash
# start.sh

# Criar diretórios necessários com permissões corretas
mkdir -p /var/www/html/storage/app/temp
mkdir -p /var/www/html/storage/framework/dompdf
mkdir -p /var/www/html/storage/framework/fonts
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

# Definir permissões
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Garante que o link de storage exista
php artisan storage:link || true

# Roda migrations (sem parar se não houver alterações)
php artisan migrate --force

# Inicia o Apache
apache2-foreground