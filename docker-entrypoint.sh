#!/bin/sh
set -e

# Sync Docker environment variables into .env (CLI vs HTTP can diverge otherwise)
sync_env() {
    key=$1
    val=$(printenv "$key" 2>/dev/null || true)
    if [ -n "$val" ]; then
        if grep -q "^${key}=" .env 2>/dev/null; then
            sed -i "s|^${key}=.*|${key}=${val}|" .env
        else
            echo "${key}=${val}" >> .env
        fi
    fi
}

for var in DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD \
           JWT_SECRET JWT_TTL \
           AUTH_SERVICE_URL INVENTORY_SERVICE_URL ORDER_SERVICE_URL \
           CUSTOMER_SERVICE_URL FINANCE_SERVICE_URL \
           INTERNAL_SERVICE_TOKEN \
           RABBITMQ_HOST RABBITMQ_PORT RABBITMQ_USER RABBITMQ_PASSWORD RABBITMQ_QUEUE \
           APP_KEY; do
    sync_env "$var"
done

if [ -n "$DB_HOST" ]; then
    echo "→ Waiting for database..."
    sleep 5

    echo "→ Running migrations..."
    php artisan migrate --force

    if [ "$SEED_DATABASE" = "true" ]; then
        echo "→ Seeding database..."
        php artisan db:seed --force || true
    fi
else
    echo "→ No database configured — skipping migrations."
fi

echo "→ Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan cache:clear 2>/dev/null || true

echo "→ Starting service..."
exec "$@"
