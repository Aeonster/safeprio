FROM php:8.2-apache

# Installera system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# Installera PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    gd \
    mysqli \
    pdo \
    pdo_mysql \
    zip

# Aktivera Apache mod_rewrite
RUN a2enmod rewrite

# Sätt working directory
WORKDIR /var/www/html

# Konfigurera Apache för UTF-8
RUN echo "AddDefaultCharset UTF-8" >> /etc/apache2/apache2.conf

# Sätt PHP charset till UTF-8
RUN echo "default_charset = \"UTF-8\"" >> /usr/local/etc/php/conf.d/charset.ini

# Kopiera Apache config (om du vill anpassa)
# COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Sätt rättigheter
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Exponera port 80
EXPOSE 80
