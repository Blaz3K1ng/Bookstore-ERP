<?php
function test($label, $url, $token = null) {
    $opts = [
        'http' => [
            'method' => 'GET',
            'header' => "Accept: application/json\r\n" . ($token ? "Authorization: Bearer $token\r\n" : ''),
            'ignore_errors' => true
        ],
        'ssl' => ['verify_peer' => false]
    ];
    $ctx = stream_context_create($opts);
    $resp = file_get_contents($url, false, $ctx);
    $code = explode(' ', $http_response_header[0])[1];
    $icon = ($code >= 200 && $code < 300) ? '✅' : '❌';
    echo "$icon [$code] $label\n";
    echo "   " . substr($resp, 0, 300) . "\n\n";
    return json_decode($resp, true);
}

$base = 'https://bookstore-frontend-26nw.onrender.com';

// Login first
$opts = ['http' => ['method' => 'POST', 'header' => "Content-Type: application/json\r\nAccept: application/json\r\n", 'content' => json_encode(['email'=>'admin@pageturn.com','password'=>'password123']), 'ignore_errors' => true], 'ssl' => ['verify_peer'=>false]];
$loginRes = json_decode(file_get_contents("$base/api/v1/auth/login", false, stream_context_create($opts)), true);
$token = $loginRes['token'];

echo "=== Inspecting API Response Shapes ===\n\n";
test('Monthly Revenue (finance route)', "$base/api/v1/reports/revenue/monthly", $token);
test('Top Books (reporting service)',   "$base/api/v1/reports/top-books",        $token);
test('Low Stock (reporting service)',   "$base/api/v1/reports/low-stock",        $token);
test('Reports Dashboard',              "$base/api/v1/reports/dashboard",        $token);
