FROM php:8.3-fpm

# Cài các extension cần thiết
RUN apt-get update && apt-get install -y \
  build-essential \
  libpng-dev \
  libjpeg-dev \
  libfreetype6-dev \
  libonig-dev \
  libxml2-dev \
  zip \
  unzip \
  curl \
  git \
  && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd opcache

# Copy file opcache.ini
COPY ./docker/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Cài Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
