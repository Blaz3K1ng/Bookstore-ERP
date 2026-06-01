<?php
$data = json_encode(['email' => 'admin@pageturn.com', 'password' => 'password123']);
$opts = [
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\nAccept: application/json\r\n",
        'content' => $data,
        'ignore_errors' => true
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false
    ]
];
$context = stream_context_create($opts);
$login_response = file_get_contents('https://bookstore-erp.onrender.com/api/v1/auth/login', false, $context);
$login_data = json_decode($login_response, true);
$token = $login_data['token'] ?? null;

if (!$token) {
    die("Login failed: $login_response\n");
}

echo "Login Success! Role: " . $login_data['data']['role'] . "\n";

$opts2 = [
    'http' => [
        'method' => 'GET',
        'header' => "Authorization: Bearer $token\r\nAccept: application/json\r\n",
        'ignore_errors' => true
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false
    ]
];
$context2 = stream_context_create($opts2);
$reports_response = file_get_contents('https://bookstore-erp.onrender.com/api/v1/reports/revenue', false, $context2);
echo "Reports Response:\n$reports_response\n";
