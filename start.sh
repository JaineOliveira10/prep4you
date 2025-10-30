#!/bin/bash
# start.sh

# Garante que o link de storage exista
php artisan storage:link || true

# Roda migrations (sem parar se não houver alterações)
php artisan migrate --force

# Inicia o Apache
apache2-foreground
