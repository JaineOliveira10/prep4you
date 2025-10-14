# Base PHP 8.3 com Apache
FROM php:8.3-apache

# Instala extensões e dependências do sistema
RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo pdo_pgsql \
    && apt-get clean

# Habilita mod_rewrite do Apache
RUN a2enmod rewrite

# Instala Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Define diretório de trabalho
WORKDIR /var/www/html

# Copia código do projeto
COPY . /var/www/html

# Ajusta permissões de storage e cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Configura Apache para servir a pasta public
RUN sed -i 's#/var/www/html#/var/www/html/public#g' /etc/apache2/sites-available/000-default.conf

# Instala dependências PHP
RUN composer install --no-dev --optimize-autoloader

# Roda todas as migrations
RUN php artisan migrate --force

# Expõe porta padrão do Render
EXPOSE 10000

# Comando padrão do container
CMD ["apache2-foreground"]
