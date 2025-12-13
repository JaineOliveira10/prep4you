#!/bin/bash
# start.sh

# Criar diretórios necessários com permissões corretas
mkdir -p /var/www/html/storage/app/temp
mkdir -p /var/www/html/storage/framework/dompdf
mkdir -p /var/www/html/storage/framework/fonts
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache
mkdir -p /var/www/html/resources/fonts

# Definir permissões
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/resources/fonts
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/resources/fonts

# Garanta que Laravel pode escrever nos diretórios críticos
chmod -R 777 /var/www/html/storage/framework/dompdf 2>/dev/null || true
chmod -R 777 /var/www/html/storage/framework/fonts 2>/dev/null || true
chmod -R 777 /var/www/html/storage/logs 2>/dev/null || true

# Garante que o link de storage exista
php artisan storage:link || true

# Limpar cache
php artisan config:clear || true
php artisan view:clear || true

# Roda migrations (sem parar se não houver alterações)
php artisan migrate --force

# Inicia o Apache
apache2-foreground