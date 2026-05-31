<?php
/**
 * Post-scaffold configuration for PageCraft Bookstore ERP microservices.
 * Usage: php scripts/configure-service.php <service-name>
 */

$service = $argv[1] ?? null;
if (! $service || ! is_dir($service)) {
    fwrite(STDERR, "Usage: php scripts/configure-service.php <service-directory>\n");
    exit(1);
}

$root = realpath($service);
$type = detectType($service);

echo "Configuring {$service} ({$type})...\n";

// Remove default Laravel migrations that conflict
foreach (glob("{$root}/database/migrations/*create_users_table.php") as $file) {
    if (str_contains($file, '2014_10_12')) {
        unlink($file);
    }
}
foreach (glob("{$root}/database/migrations/*create_password_reset_tokens_table.php") as $file) {
    unlink($file);
}

// CORS
copy('service-files/shared/cors.php', "{$root}/config/cors.php");

// Health controller + request logging (all services)
copy('service-files/shared/HealthController.php', "{$root}/app/Http/Controllers/HealthController.php");
copy('service-files/shared/LogRequest.php', "{$root}/app/Http/Middleware/LogRequest.php");

// Middleware aliases in Kernel.php
$kernelPath = "{$root}/app/Http/Kernel.php";
$kernel = file_get_contents($kernelPath);

$aliases = [
    "'role'" => "'role' => \\App\\Http\\Middleware\\CheckRole::class",
    "'jwt.verify'" => "'jwt.verify' => \\App\\Http\\Middleware\\VerifyJwtToken::class",
    "'service.auth'" => "'service.auth' => \\App\\Http\\Middleware\\VerifyServiceAuth::class",
    "'gateway.auth'" => "'gateway.auth' => \\App\\Http\\Middleware\\AuthenticateGateway::class",
];

foreach ($aliases as $key => $line) {
    if (! str_contains($kernel, $key)) {
        $kernel = preg_replace(
            '/(\$middlewareAliases\s*=\s*\[)/',
            "$1\n        {$line},",
            $kernel,
            1
        );
    }
}

if (! str_contains($kernel, 'LogRequest')) {
    $kernel = preg_replace(
        '/(\$middleware\s*=\s*\[)/',
        "$1\n        \\App\\Http\\Middleware\\LogRequest::class,",
        $kernel,
        1
    );
}

file_put_contents($kernelPath, $kernel);

// Auth service: JWT guard
if ($type === 'auth') {
    copy('service-files/auth-service/auth.php', "{$root}/config/auth.php");

    $providers = file_get_contents("{$root}/config/app.php");
    if (! str_contains($providers, 'Tymon\\JWTAuth')) {
        $providers = str_replace(
            "App\\Providers\\RouteServiceProvider::class,",
            "App\\Providers\\RouteServiceProvider::class,\n        Tymon\\JWTAuth\\Providers\\LaravelServiceProvider::class,",
            $providers
        );
        file_put_contents("{$root}/config/app.php", $providers);
    }
}

// Copy shared middleware where needed
if (in_array($type, ['inventory', 'order', 'customer', 'finance'])) {
    copy('service-files/shared/VerifyJwtToken.php', "{$root}/app/Http/Middleware/VerifyJwtToken.php");
    copy('service-files/shared/CheckRole.php', "{$root}/app/Http/Middleware/CheckRole.php");
}

if (in_array($type, ['inventory', 'customer'])) {
    copy('service-files/shared/VerifyServiceAuth.php', "{$root}/app/Http/Middleware/VerifyServiceAuth.php");
}

if ($type === 'gateway') {
    copy('service-files/api-gateway/AuthenticateGateway.php', "{$root}/app/Http/Middleware/AuthenticateGateway.php");
}

// .env additions
$envPath = "{$root}/.env";
if (file_exists($envPath)) {
    $env = file_get_contents($envPath);
    $additions = getEnvAdditions($type);
    foreach ($additions as $key => $value) {
        if (! preg_match("/^{$key}=/m", $env)) {
            $env .= "\n{$key}={$value}";
        }
    }
    file_put_contents($envPath, $env);
}

echo "  Done.\n";

function detectType(string $service): string
{
    return match (true) {
        str_contains($service, 'auth')      => 'auth',
        str_contains($service, 'inventory') => 'inventory',
        str_contains($service, 'order')     => 'order',
        str_contains($service, 'customer')  => 'customer',
        str_contains($service, 'finance')   => 'finance',
        str_contains($service, 'gateway')   => 'gateway',
        default => 'generic',
    };
}

function getEnvAdditions(string $type): array
{
    $shared = [
        'AUTH_SERVICE_URL' => 'http://auth-service:8000',
        'INTERNAL_SERVICE_TOKEN' => 'pagecraft-internal-dev-token',
    ];

    return match ($type) {
        'auth' => array_merge($shared, [
            'JWT_SECRET' => 'pagecraft-jwt-secret-change-in-production-64chars!!',
            'JWT_TTL' => '60',
        ]),
        'inventory' => $shared,
        'order' => array_merge($shared, [
            'INVENTORY_SERVICE_URL' => 'http://inventory-service:8000',
            'CUSTOMER_SERVICE_URL' => 'http://customer-service:8000',
            'RABBITMQ_HOST' => 'rabbitmq',
            'RABBITMQ_PORT' => '5672',
            'RABBITMQ_USER' => 'guest',
            'RABBITMQ_PASSWORD' => 'guest',
            'RABBITMQ_QUEUE' => 'order.created',
        ]),
        'customer' => $shared,
        'finance' => array_merge($shared, [
            'RABBITMQ_HOST' => 'rabbitmq',
            'RABBITMQ_PORT' => '5672',
            'RABBITMQ_USER' => 'guest',
            'RABBITMQ_PASSWORD' => 'guest',
            'RABBITMQ_QUEUE' => 'order.created',
        ]),
        'gateway' => [
            'AUTH_SERVICE_URL' => 'http://auth-service:8000',
            'INVENTORY_SERVICE_URL' => 'http://inventory-service:8000',
            'ORDER_SERVICE_URL' => 'http://order-service:8000',
            'CUSTOMER_SERVICE_URL' => 'http://customer-service:8000',
            'FINANCE_SERVICE_URL' => 'http://finance-service:8000',
        ],
        default => [],
    };
}
