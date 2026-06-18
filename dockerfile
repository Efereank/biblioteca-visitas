# --- Etapa 1: Construcción ---
FROM php:8.3-fpm AS builder

RUN apt-get update && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev libzip-dev zip unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# --- Etapa 2: Imagen Final (más ligera) ---
FROM php:8.3-fpm-alpine

# Instalar solo las dependencias necesarias en tiempo de ejecución (Alpine es mucho más ligero)
RUN apk add --no-cache libpng libonig libxml2 libzip

# Copiar solo las extensiones compiladas y configuraciones desde la etapa anterior
COPY --from=builder /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=builder /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d

WORKDIR /var/www

# Copiar el código fuente (asegúrate de tener un .dockerignore correcto)
COPY . .

RUN chown -R www-data:www-data /var/www

EXPOSE 9000
CMD ["php-fpm"]
