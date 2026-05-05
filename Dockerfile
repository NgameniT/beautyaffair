FROM php:8.4-apache

# Extensions système nécessaires
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libicu-dev libzip-dev libonig-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
        pdo pdo_pgsql intl zip mbstring opcache \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Opcache optimisé pour la prod
RUN echo "opcache.enable=1\nopcache.memory_consumption=128\nopcache.max_accelerated_files=10000\nopcache.revalidate_freq=0" \
    >> /usr/local/etc/php/conf.d/opcache.ini

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copie le code
COPY . .

# Dépendances PHP (prod uniquement)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts \
    && composer dump-autoload --optimize --no-dev

# Dossiers nécessaires
RUN mkdir -p var/cache var/log public/uploads/produits \
    && chown -R www-data:www-data var public/uploads

# Config Apache
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Script de démarrage
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/entrypoint.sh"]
