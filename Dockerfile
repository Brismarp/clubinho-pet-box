FROM php:8.2-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip curl libzip-dev \
    && docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && printf '<Directory ${APACHE_DOCUMENT_ROOT}>\n    AllowOverride All\n    Require all granted\n    DirectoryIndex index.html index.php\n</Directory>\n' \
        > /etc/apache2/conf-available/allow-override.conf \
    && a2enconf allow-override

WORKDIR /var/www/html

COPY composer.json ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

COPY . .
RUN composer dump-autoload -o \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80
