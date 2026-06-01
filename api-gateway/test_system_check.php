<?php
function test($label, $url, $method = 'GET', $token = null, $body = null) {
    $opts = [
        'http' => [
            'method' => $method,
            'header' => "Accept: application/json\r\n" .
                        ($token ? "Authorization: Bearer $token\r\n" : '') .
                        ($body ? "Content-Type: application/json\r\n" : ''),
            'content' => $body,
            'ignore_errors' => true
        ],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
    ];
    $ctx = stream_context_create($opts);
    $resp = file_get_contents($url, false, $ctx);
    $code = explode(' ', $http_response_header[0])[1];
    $icon = ($code >= 200 && $code < 300) ? '✅' : '❌';
    echo "$icon [$code] $label\n";
    if ($code >= 300) echo "   Response: " . substr($resp, 0, 200) . "\n";
    return json_decode($resp, true);
}

// The frontend is the API gateway in this setup!
$base = 'https://bookstore-frontend-26nw.onrender.com';

echo "=== Testing Login ===\n";
$loginRes = test('Admin Login', "$base/api/v1/auth/login", 'POST', null,
    json_encode(['email' => 'admin@pageturn.com', 'password' => 'password123']));
$token = $loginRes['token'] ?? null;
$role  = $loginRes['data']['role'] ?? 'unknown';
echo "   Role: $role\n";

if (!$token) { die("Cannot continue without token\n"); }

echo "\n=== Testing API Endpoints via frontend proxy ===\n";
test('Revenue Report',    "$base/api/v1/reports/revenue",    'GET', $token);
test('Reports Dashboard', "$base/api/v1/reports/dashboard",  'GET', $token);
test('Top Books',         "$base/api/v1/reports/top-books",  'GET', $token);
test('Low Stock',         "$base/api/v1/reports/low-stock",  'GET', $token);
test('Invoices',          "$base/api/v1/invoices",           'GET', $token);
test('Suppliers',         "$base/api/v1/suppliers",          'GET', $token);
test('Orders',            "$base/api/v1/orders",             'GET', $token);
test('Customers',         "$base/api/v1/customers",          'GET', $token);
test('Books',             "$base/api/v1/books",              'GET', $token);
test('Stock Alerts',      "$base/api/v1/stock/alerts",       'GET', $token);

echo "\n=== Done ===\n";
