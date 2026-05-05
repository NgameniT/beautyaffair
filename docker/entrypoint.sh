#!/bin/sh
set -e

echo "==> Vider le cache Symfony (prod)..."
php bin/console cache:clear --env=prod --no-debug || true

echo "==> Préchauffer le cache..."
php bin/console cache:warmup --env=prod --no-debug || true

echo "==> Migrations base de données..."
php bin/console doctrine:migrations:migrate --no-interaction --env=prod || true

echo "==> Permissions uploads..."
mkdir -p /var/www/html/public/uploads/produits
chown -R www-data:www-data /var/www/html/var /var/www/html/public/uploads

echo "==> Démarrage Apache..."
exec apache2-foreground
