FROM php:8.3-cli

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql sockets

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
