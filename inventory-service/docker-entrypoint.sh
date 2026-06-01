#!/bin/sh
set -e

# Ensure .env exists — on Render/CI the .env file is not committed
if [ ! -f .env ]; then
    cp .env.example .env 2>/dev/null || touch .env
fi

# Normalize DATABASE_URL for Neon compatibility (strip channel_binding, normalize scheme)
if [ -n "$DATABASE_URL" ]; then
    normalized=$(php -r '$url=getenv("DATABASE_URL"); if(!$url){exit;} $url=preg_replace("#^postgresql://#","postgres://",$url); $parts=parse_url($url); if(!$parts){echo $url; exit;} $query=$parts["query"] ?? ""; parse_str($query,$q); unset($q["channel_binding"]); $newQuery=http_build_query($q); $scheme=$parts["scheme"] ?? ""; $user=$parts["user"] ?? ""; $pass=$parts["pass"] ?? ""; $auth=$user!=="" ? $user . ($pass!=="" ? ":" . $pass : "") . "@" : ""; $host=$parts["host"] ?? ""; $port=isset($parts["port"]) ? ":" . $parts["port"] : ""; $path=$parts["path"] ?? ""; $frag=isset($parts["fragment"]) ? "#" . $parts["fragment"] : ""; echo $scheme . "://" . $auth . $host . $port . $path . ($newQuery!=="" ? "?" . $newQuery : "") . $frag;') || normalized="$DATABASE_URL"
    if [ -n "$normalized" ] && [ "$normalized" != "$DATABASE_URL" ]; then
        echo "→ Normalized DATABASE_URL for compatibility."
    fi
    DATABASE_URL="$normalized"
    export DATABASE_URL
fi

echo "LOG_CHANNEL=stderr" >> .env
# Auto-detect DB_CONNECTION from DATABASE_URL scheme (Render provides postgres://)
if [ -z "$DB_CONNECTION" ] && [ -n "$DATABASE_URL" ]; then
    case "$DATABASE_URL" in
        postgres://*|postgresql://*) DB_CONNECTION=pgsql ;;
        mysql://*) DB_CONNECTION=mysql ;;
    esac
    export DB_CONNECTION
fi

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

for var in DB_CONNECTION DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD DATABASE_URL \
           JWT_SECRET JWT_TTL \
           AUTH_SERVICE_URL INVENTORY_SERVICE_URL ORDER_SERVICE_URL \
           CUSTOMER_SERVICE_URL FINANCE_SERVICE_URL SUPPLIER_SERVICE_URL REPORTING_SERVICE_URL \
           INTERNAL_SERVICE_TOKEN \
           RABBITMQ_HOST RABBITMQ_PORT RABBITMQ_USER RABBITMQ_PASSWORD RABBITMQ_QUEUE NOTIFICATION_QUEUE \
           APP_KEY APP_ENV APP_DEBUG LOG_CHANNEL; do
    sync_env "$var"
done

if ! grep -Eq '^APP_KEY=base64:[A-Za-z0-9+/=]{44}$' .env; then
    echo "→ Generating valid APP_KEY (provided key is missing or invalid length)..."
    php artisan key:generate --force
fi

if [ -n "$DB_HOST" ] || [ -n "$DATABASE_URL" ]; then
    echo "→ Waiting for database..."
    sleep 5

    echo "→ Running migrations..."
    php artisan migrate --force -v

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





