#!/bin/bash
# filepath: deploy-local.sh

# 1. Levanta el contenedor de PostgreSQL
docker run -d --name quickrestaurant-pg \
  -e POSTGRES_PASSWORD=secret \
  -p 5432:5432 \
  -v quickrestaurant_data:/var/lib/postgresql/data \
  postgres:16-alpine

# 2. Espera a que PostgreSQL esté listo
echo "Esperando a que PostgreSQL inicie..."
until docker exec quickrestaurant-pg pg_isready -U postgres; do
  sleep 1
done

# 3. Crea la base de datos si no existe
docker exec -u postgres quickrestaurant-pg psql -c "CREATE DATABASE quickrestaurant;" 2>/dev/null

# 4. Carga el archivo SQL demo
echo "Cargando quickrestaurant.sql..."
docker cp quickrestaurant.sql quickrestaurant-pg:/quickrestaurant.sql
docker exec -u postgres quickrestaurant-pg psql -U postgres -d quickrestaurant -f /quickrestaurant.sql
docker exec -u postgres quickrestaurant-pg psql -U postgres -d quickrestaurant -c "SELECT setval('categories_id_seq', (SELECT MAX(id) FROM categories));"
docker exec -u postgres quickrestaurant-pg psql -U postgres -d quickrestaurant -c "SELECT setval('dishes_id_seq', (SELECT MAX(id) FROM dishes));"
docker exec -u postgres quickrestaurant-pg psql -U postgres -d quickrestaurant -c "SELECT setval('orders_id_seq', (SELECT MAX(id) FROM orders));"
docker exec -u postgres quickrestaurant-pg psql -U postgres -d quickrestaurant -c "SELECT setval('order_items_id_seq', (SELECT MAX(id) FROM order_items));"
docker exec -u postgres quickrestaurant-pg psql -U postgres -d quickrestaurant -c "SELECT setval('users_id_seq', (SELECT MAX(id) FROM users));"

# 5. Instala dependencias PHP si es necesario
if [ ! -d vendor ]; then
  composer install
fi

# 6. Genera la clave de la app si no existe
if ! grep -q "APP_KEY=base64" .env; then
  php artisan key:generate
fi

# 7. Limpia cachés de Laravel
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 8. Listo para correr el servidor
echo "¡Listo! Ejecuta: php artisan serve"