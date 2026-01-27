FROM composer:2 AS vendor
WORKDIR /app
COPY . .
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

FROM node:20-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json* vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm install
RUN npm run build

FROM php:8.2-fpm-alpine
WORKDIR /var/www

RUN apk add --no-cache \
    bash \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pdo_mysql \
        zip \
    && rm -rf /var/cache/apk/*

COPY --from=vendor /app /var/www
COPY --from=frontend /app/public/build /var/www/public/build

COPY php.ini /usr/local/etc/php/conf.d/laravel.ini
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]