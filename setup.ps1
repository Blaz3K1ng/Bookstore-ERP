# PageCraft Bookstore ERP — Windows Setup
$ErrorActionPreference = "Stop"
Write-Host "=== PageCraft Bookstore ERP Setup ===" -ForegroundColor Green

$services = @("api-gateway","auth-service","inventory-service","order-service","customer-service","finance-service","supplier-service","reporting-service")

foreach ($svc in $services) {
    if (Test-Path "$svc\vendor") {
        Write-Host "[WARN] $svc already exists - skipping scaffold." -ForegroundColor Yellow
    } else {
        Write-Host "[INFO] Creating Laravel 10 project: $svc ..."
        composer create-project laravel/laravel:^10.0 $svc --prefer-dist --quiet --no-interaction
    }
}

Write-Host "[INFO] Installing auth-service packages..."
Push-Location auth-service
composer require tymon/jwt-auth --quiet --no-interaction
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider" --quiet
Pop-Location

Write-Host "[INFO] Installing order-service packages..."
Push-Location order-service
composer require php-amqplib/php-amqplib guzzlehttp/guzzle --quiet --no-interaction
Pop-Location

Write-Host "[INFO] Installing finance-service packages..."
Push-Location finance-service
composer require php-amqplib/php-amqplib --quiet --no-interaction
Pop-Location

foreach ($svc in @("inventory-service","customer-service","api-gateway","supplier-service")) {
    Write-Host "[INFO] Installing guzzle for $svc ..."
    Push-Location $svc
    composer require guzzlehttp/guzzle --quiet --no-interaction
    Pop-Location
}

Write-Host "[INFO] Copying source files..."

# Auth
Copy-Item service-files/auth-service/routes/api.php auth-service/routes/api.php -Force
Copy-Item service-files/auth-service/AuthController.php auth-service/app/Http/Controllers/AuthController.php -Force
Copy-Item service-files/auth-service/User.php auth-service/app/Models/User.php -Force
Copy-Item service-files/auth-service/create_users_table.php auth-service/database/migrations/2024_01_01_000001_create_users_table.php -Force
Copy-Item service-files/auth-service/DatabaseSeeder.php auth-service/database/seeders/DatabaseSeeder.php -Force

# Inventory
Copy-Item service-files/inventory-service/routes/api.php inventory-service/routes/api.php -Force
Copy-Item service-files/inventory-service/BookController.php inventory-service/app/Http/Controllers/BookController.php -Force
Copy-Item service-files/inventory-service/Book.php inventory-service/app/Models/Book.php -Force
Copy-Item service-files/inventory-service/create_books_table.php inventory-service/database/migrations/2024_01_01_000001_create_books_table.php -Force
Copy-Item service-files/inventory-service/DatabaseSeeder.php inventory-service/database/seeders/DatabaseSeeder.php -Force

# Order
Copy-Item service-files/order-service/routes/api.php order-service/routes/api.php -Force
Copy-Item service-files/order-service/OrderController.php order-service/app/Http/Controllers/OrderController.php -Force
Copy-Item service-files/order-service/Order.php order-service/app/Models/Order.php -Force
Copy-Item service-files/order-service/OrderItem.php order-service/app/Models/OrderItem.php -Force
Copy-Item service-files/order-service/create_orders_table.php order-service/database/migrations/2024_01_01_000001_create_orders_table.php -Force
Copy-Item service-files/order-service/create_order_items_table.php order-service/database/migrations/2024_01_01_000002_create_order_items_table.php -Force
New-Item -ItemType Directory -Force -Path order-service/app/Services | Out-Null
Copy-Item service-files/order-service/InventoryService.php order-service/app/Services/InventoryService.php -Force
Copy-Item service-files/order-service/FinancePublisher.php order-service/app/Services/FinancePublisher.php -Force
Copy-Item service-files/order-service/CustomerService.php order-service/app/Services/CustomerService.php -Force

# Customer
Copy-Item service-files/customer-service/routes/api.php customer-service/routes/api.php -Force
Copy-Item service-files/customer-service/CustomerController.php customer-service/app/Http/Controllers/CustomerController.php -Force
Copy-Item service-files/customer-service/Customer.php customer-service/app/Models/Customer.php -Force
Copy-Item service-files/customer-service/create_customers_table.php customer-service/database/migrations/2024_01_01_000001_create_customers_table.php -Force
Copy-Item service-files/customer-service/DatabaseSeeder.php customer-service/database/seeders/DatabaseSeeder.php -Force

# Finance
Copy-Item service-files/finance-service/routes/api.php finance-service/routes/api.php -Force
Copy-Item service-files/finance-service/InvoiceController.php finance-service/app/Http/Controllers/InvoiceController.php -Force
Copy-Item service-files/finance-service/Invoice.php finance-service/app/Models/Invoice.php -Force
Copy-Item service-files/finance-service/create_invoices_table.php finance-service/database/migrations/2024_01_01_000001_create_invoices_table.php -Force
New-Item -ItemType Directory -Force -Path finance-service/app/Console/Commands | Out-Null
Copy-Item service-files/finance-service/ConsumeOrderEvents.php finance-service/app/Console/Commands/ConsumeOrderEvents.php -Force

# Gateway
Copy-Item service-files/api-gateway/routes/api.php api-gateway/routes/api.php -Force
Copy-Item service-files/api-gateway/GatewayController.php api-gateway/app/Http/Controllers/GatewayController.php -Force

# Supplier Service
New-Item -ItemType Directory -Force -Path supplier-service/app/Http/Controllers | Out-Null
New-Item -ItemType Directory -Force -Path supplier-service/app/Http/Middleware | Out-Null
New-Item -ItemType Directory -Force -Path supplier-service/app/Models | Out-Null
New-Item -ItemType Directory -Force -Path supplier-service/app/Services | Out-Null
New-Item -ItemType Directory -Force -Path supplier-service/database/migrations | Out-Null
New-Item -ItemType Directory -Force -Path supplier-service/database/seeders | Out-Null
Copy-Item service-files/supplier-service/routes/api.php supplier-service/routes/api.php -Force
Copy-Item service-files/supplier-service/app/Http/Controllers/* supplier-service/app/Http/Controllers/ -Force
Copy-Item service-files/supplier-service/app/Http/Middleware/* supplier-service/app/Http/Middleware/ -Force
Copy-Item service-files/supplier-service/app/Http/Kernel.php supplier-service/app/Http/Kernel.php -Force
Copy-Item service-files/supplier-service/app/Models/* supplier-service/app/Models/ -Force
Copy-Item service-files/supplier-service/app/Services/* supplier-service/app/Services/ -Force
Copy-Item service-files/supplier-service/database/migrations/* supplier-service/database/migrations/ -Force
Copy-Item service-files/supplier-service/database/seeders/* supplier-service/database/seeders/ -Force

# Reporting Service
New-Item -ItemType Directory -Force -Path reporting-service/app/Http/Controllers | Out-Null
New-Item -ItemType Directory -Force -Path reporting-service/app/Http/Middleware | Out-Null
New-Item -ItemType Directory -Force -Path reporting-service/app/Services | Out-Null
Copy-Item service-files/reporting-service/routes/api.php reporting-service/routes/api.php -Force
Copy-Item service-files/reporting-service/app/Http/Controllers/* reporting-service/app/Http/Controllers/ -Force
Copy-Item service-files/reporting-service/app/Http/Middleware/* reporting-service/app/Http/Middleware/ -Force
Copy-Item service-files/reporting-service/app/Http/Kernel.php reporting-service/app/Http/Kernel.php -Force
Copy-Item service-files/reporting-service/app/Services/* reporting-service/app/Services/ -Force

foreach ($svc in $services) {
    Copy-Item Dockerfile "$svc/Dockerfile" -Force
    Copy-Item docker-entrypoint.sh "$svc/docker-entrypoint.sh" -Force
}

Write-Host "[INFO] Generating .env files and configuring..."
foreach ($svc in $services) {
    if (-not (Test-Path "$svc\.env")) {
        Copy-Item "$svc\.env.example" "$svc\.env"
        Push-Location $svc
        php artisan key:generate --quiet
        Pop-Location
    }
    php scripts/configure-service.php $svc
}

Push-Location auth-service
php artisan jwt:secret --force --quiet
Pop-Location

Write-Host ""
Write-Host "Setup complete!" -ForegroundColor Green
Write-Host "  docker compose up --build -d"
Write-Host "  API Gateway: http://localhost:8000/api/v1/"
Write-Host "  Frontend:    http://localhost:3000"
