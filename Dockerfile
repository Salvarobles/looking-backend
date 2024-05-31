FROM php:8.3.4-fpm

# Instalar extensiones y herramientas necesarias
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    unzip \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar directorio de trabajo
WORKDIR /var/www/html

# Copiar el código de la aplicación Symfony
COPY . /var/www/html

# Instalar las dependencias de Composer
RUN composer install

# Exponer el puerto 9000 para PHP-FPM
EXPOSE 8000

# Comando para ejecutar PHP-FPM
CMD ["php-fpm"]