# --- Etapa 1: Build de Frontend (Node.js) ---
FROM node:20-alpine AS node-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# --- Etapa 2: Build de Dependencias (Composer) ---
FROM php:8.3-fpm-alpine AS composer-builder
RUN apk add --no-cache unzip libzip-dev
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# --- Etapa 3: Imagen Final (Producción) ---
FROM php:8.3-fpm-alpine

# Instalar dependencias de ejecución
RUN apk add --no-cache \
    libpng \
    libonig \
    libxml2 \
    libzip \
    mysql-client

# Instalar extensiones PHP necesarias
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

WORKDIR /var/www

# Copiar archivos desde las etapas anteriores
COPY --from=composer-builder /app/vendor ./vendor
COPY --from=node-builder /app/public/build ./public/build
COPY . .

# Configurar permisos para Laravel
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
