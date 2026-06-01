<?php
$data = json_encode([
    'name' => 'Test User',
    'email' => 'test_user_unique_123@pageturn.com',
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'role' => 'customer'
]);
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
$result = file_get_contents('https://bookstore-erp.onrender.com/api/v1/auth/register', false, $context);
echo "Response:\n$result\n";
