# Base PHP 8.3 com Apache
FROM php:8.3-apache

# Instala extensões e dependências do sistema
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    unzip \
    git \
    curl \
    nodejs npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_pgsql \
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

# Build dos assets front-end (Vite)
RUN npm install
RUN npm run build

# Expõe porta padrão do Render
EXPOSE 10000

# Comando padrão do container
CMD ["./start.sh"]
