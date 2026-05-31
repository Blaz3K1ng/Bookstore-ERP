#!/bin/bash
# PageCraft Bookstore ERP — Project Setup Script
# Run this once to scaffold all Laravel microservices.
# Requires: PHP 8.2+, Composer, Docker, Docker Compose

set -e

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'

info()    { echo -e "${GREEN}[INFO]${NC} $1"; }
warning() { echo -e "${YELLOW}[WARN]${NC} $1"; }
error()   { echo -e "${RED}[ERR]${NC}  $1"; exit 1; }

SERVICES=("api-gateway" "auth-service" "inventory-service" "order-service" "customer-service" "finance-service")

info "=== PageCraft Bookstore ERP Setup ==="
echo ""

# ─── 1. Scaffold each Laravel project ────────────────────────────
for svc in "${SERVICES[@]}"; do
    if [ -d "$svc/vendor" ]; then
        warning "$svc already exists — skipping scaffold."
    else
        info "Creating Laravel project: $svc ..."
        composer create-project laravel/laravel "$svc" --prefer-dist --quiet
    fi
done

# ─── 2. Install per-service Composer packages ─────────────────────
info "Installing auth-service packages..."
(cd auth-service && composer require tymon/jwt-auth --quiet && php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider" --quiet)

info "Installing order-service packages..."
(cd order-service && composer require php-amqplib/php-amqplib guzzlehttp/guzzle --quiet)

info "Installing finance-service packages..."
(cd finance-service && composer require php-amqplib/php-amqplib --quiet)

for svc in "inventory-service" "customer-service" "api-gateway"; do
    info "Installing guzzle for $svc ..."
    (cd "$svc" && composer require guzzlehttp/guzzle --quiet)
done

# ─── 3. Copy project source files ────────────────────────────────
info "Copying source files into each service..."

copy_service_file() {
    local svc=$1; local src=$2; local dst=$3
    local dir; dir=$(dirname "$dst")
    mkdir -p "$svc/$dir"
    cp "service-files/$svc/$src" "$svc/$dst"
}

# Auth Service
cp service-files/auth-service/routes/api.php         auth-service/routes/api.php
cp service-files/auth-service/AuthController.php     auth-service/app/Http/Controllers/AuthController.php
cp service-files/auth-service/User.php               auth-service/app/Models/User.php
cp service-files/auth-service/create_users_table.php auth-service/database/migrations/2024_01_01_000001_create_users_table.php

# Inventory Service
cp service-files/inventory-service/routes/api.php           inventory-service/routes/api.php
cp service-files/inventory-service/BookController.php       inventory-service/app/Http/Controllers/BookController.php
cp service-files/inventory-service/Book.php                 inventory-service/app/Models/Book.php
cp service-files/inventory-service/create_books_table.php   inventory-service/database/migrations/2024_01_01_000001_create_books_table.php
cp service-files/shared/VerifyJwtToken.php                  inventory-service/app/Http/Middleware/VerifyJwtToken.php

# Order Service
cp service-files/order-service/routes/api.php               order-service/routes/api.php
cp service-files/order-service/OrderController.php          order-service/app/Http/Controllers/OrderController.php
cp service-files/order-service/Order.php                    order-service/app/Models/Order.php
cp service-files/order-service/OrderItem.php                order-service/app/Models/OrderItem.php
cp service-files/order-service/create_orders_table.php      order-service/database/migrations/2024_01_01_000001_create_orders_table.php
cp service-files/order-service/create_order_items_table.php order-service/database/migrations/2024_01_01_000002_create_order_items_table.php
cp service-files/order-service/InventoryService.php         order-service/app/Services/InventoryService.php
cp service-files/order-service/FinancePublisher.php         order-service/app/Services/FinancePublisher.php
cp service-files/shared/VerifyJwtToken.php                  order-service/app/Http/Middleware/VerifyJwtToken.php

# Customer Service
cp service-files/customer-service/routes/api.php              customer-service/routes/api.php
cp service-files/customer-service/CustomerController.php      customer-service/app/Http/Controllers/CustomerController.php
cp service-files/customer-service/Customer.php                customer-service/app/Models/Customer.php
cp service-files/customer-service/create_customers_table.php  customer-service/database/migrations/2024_01_01_000001_create_customers_table.php
cp service-files/shared/VerifyJwtToken.php                    customer-service/app/Http/Middleware/VerifyJwtToken.php

# Finance Service
cp service-files/finance-service/routes/api.php                finance-service/routes/api.php
cp service-files/finance-service/InvoiceController.php         finance-service/app/Http/Controllers/InvoiceController.php
cp service-files/finance-service/Invoice.php                   finance-service/app/Models/Invoice.php
cp service-files/finance-service/create_invoices_table.php     finance-service/database/migrations/2024_01_01_000001_create_invoices_table.php
cp service-files/finance-service/ConsumeOrderEvents.php        finance-service/app/Console/Commands/ConsumeOrderEvents.php
cp service-files/shared/VerifyJwtToken.php                     finance-service/app/Http/Middleware/VerifyJwtToken.php

# API Gateway
cp service-files/api-gateway/routes/api.php            api-gateway/routes/api.php
cp service-files/api-gateway/GatewayController.php     api-gateway/app/Http/Controllers/GatewayController.php
cp service-files/api-gateway/AuthenticateGateway.php   api-gateway/app/Http/Middleware/AuthenticateGateway.php

# Copy Dockerfiles and entrypoint
for svc in "${SERVICES[@]}"; do
    cp Dockerfile            "$svc/Dockerfile"
    cp docker-entrypoint.sh  "$svc/docker-entrypoint.sh"
done

# ─── 4. Generate .env files ───────────────────────────────────────
info "Generating .env files..."
for svc in "${SERVICES[@]}"; do
    if [ ! -f "$svc/.env" ]; then
        cp "$svc/.env.example" "$svc/.env"
        (cd "$svc" && php artisan key:generate --quiet)
    fi
done

# Generate JWT secret for auth-service
(cd auth-service && php artisan jwt:secret --force --quiet)

info ""
info "✓ Setup complete!"
info ""
info "Next steps:"
info "  1. Review docker-compose.yml and replace placeholder secrets."
info "  2. Run: docker compose up --build -d"
info "  3. API is available at: http://localhost:8000/api/v1/"
info "  4. RabbitMQ UI:         http://localhost:15672 (guest/guest)"
