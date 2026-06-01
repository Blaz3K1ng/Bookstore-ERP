<?php
$files = [
    'customer-service/app/Http/Middleware/CheckRole.php',
    'finance-service/app/Http/Middleware/CheckRole.php',
    'inventory-service/app/Http/Middleware/CheckRole.php',
    'order-service/app/Http/Middleware/CheckRole.php',
    'reporting-service/app/Http/Middleware/CheckRole.php',
    'service-files/reporting-service/app/Http/Middleware/CheckRole.php',
    'service-files/shared/CheckRole.php',
    'service-files/supplier-service/app/Http/Middleware/CheckRole.php',
    'supplier-service/app/Http/Middleware/CheckRole.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = str_replace(
            "if (\$user['role'] === 'admin' || \$user['role'] === 'service') {",
            "if (\$user['role'] === 'admin' || \$user['role'] === 'super_admin' || \$user['role'] === 'service') {",
            $content
        );
        file_put_contents($file, $content);
        echo "Updated $file\n";
    }
}
