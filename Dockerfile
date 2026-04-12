# ASCOM Application Image
# Production-ready multi-stage Dockerfile
# Phase 1: Environment Setup & Docker Foundation

# Stage 1: PHP with Apache base
FROM php:8.3-apache-bookworm AS php-base

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libcurl4-openssl-dev \
    zip \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    pdo \
    zip \
    intl \
    bcmath \
    gd \
    curl

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

WORKDIR /var/www/html

# Copy application files (legacy PHP)
COPY . .

# Create storage directories if they don't exist
RUN mkdir -p storage/logs storage/sessions storage/cache bootstrap/cache && \
    chown -R www-data:www-data /var/www/html

# Stage 2: Production build
FROM php-base AS production

# Configure Apache modules
RUN a2enmod rewrite headers

# Copy Apache configuration
COPY docker/apache/ascom.conf /etc/apache2/sites-available/ascom.conf
RUN a2ensite ascom.conf && a2dissite 000-default.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Expose ports
EXPOSE 80 443

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Start Apache
CMD ["apache2-foreground"]