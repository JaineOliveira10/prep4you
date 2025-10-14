#!/bin/bash
# start.sh

# Roda migrations
php artisan migrate --force

# Inicia Apache
apache2-foreground
