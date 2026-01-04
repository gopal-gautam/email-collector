# Base image
FROM php:8.2-fpm

# Arguments for composer
ARG USER=www-data
ARG UID=1000

# System deps
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    gnupg2 \
    ca-certificates \
    libicu-dev \
    && rm -rf /var/lib/apt/lists/*

# Install extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mbstring pdo pdo_mysql intl zip bcmath

# Install Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Install Node.js (LTS) & npm
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get update && apt-get install -y nodejs && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www/html

# Copy composer files first to cache dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader || true

# Copy app
COPY . .

# Ensure permissions
RUN chown -R ${USER}:${USER} /var/www/html/storage /var/www/html/bootstrap/cache || true

# Install PHP deps for the application
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# Install node modules and build assets
RUN npm install --prefix ./ && npm run build --prefix ./ || true

# Expose php-fpm port
EXPOSE 9000

# Copy entrypoint and make executable
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

USER ${USER}

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
