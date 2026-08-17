#!/bin/sh
set -e

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "Esperando a que MySQL esté disponible..."
until php -r "new PDO('mysql:host=${DB_HOST};port=${DB_PORT}', '${DB_USERNAME}', '${DB_PASSWORD}');" 2>/dev/null
do
  echo "MySQL no está listo aún, reintentando en 2s..."
  sleep 2
done
echo "MySQL está disponible."

# Genera la app key si no existe (equivalente a un secret que en FastAPI
# normalmente pasarías por variable de entorno directamente)
if [ -z "$(grep '^APP_KEY=.' .env 2>/dev/null)" ]; then
  echo "Generando APP_KEY..."
  php artisan key:generate --force
fi

echo "Corriendo migraciones..."
php artisan migrate --force

echo "Cacheando configuración..."
php artisan config:cache
php artisan route:cache

echo "Generando documentación Swagger..."
php artisan l5-swagger:generate

echo "Listo. Iniciando proceso principal: $@"
exec "$@"
