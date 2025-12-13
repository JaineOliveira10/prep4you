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

# Configura Apache para ouvir na porta 10000 (necessário para Render)
RUN sed -i 's/80/10000/g' /etc/apache2/ports.conf
RUN sed -i 's/80/10000/g' /etc/apache2/sites-available/000-default.conf

# Instala Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Define diretório de trabalho
WORKDIR /var/www/html

# Copia código do projeto
COPY . /var/www/html

# Configura Apache para servir a pasta public
RUN sed -i 's#/var/www/html#/var/www/html/public#g' /etc/apache2/sites-available/000-default.conf

# Cria diretórios necessários pré-build
RUN mkdir -p /var/www/html/storage/framework/dompdf \
    && mkdir -p /var/www/html/storage/framework/fonts \
    && mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/storage/app/temp \
    && mkdir -p /var/www/html/resources/fonts \
    && mkdir -p /var/www/html/bootstrap/cache

# Ajusta permissões de storage, bootstrap/cache e temp
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/resources/fonts && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/resources/fonts

# Instala dependências PHP
RUN composer install --no-dev --optimize-autoloader

# Build dos assets front-end (Vite)
RUN npm install
RUN npm run build

# Expõe porta padrão do Render
EXPOSE 10000

# Comando padrão do container
CMD ["./start.sh"]
