<?php
$data = json_encode(['email' => 'test_user_unique_123@pageturn.com', 'password' => 'password123']);
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
$result = file_get_contents('https://bookstore-erp.onrender.com/api/v1/auth/login', false, $context);
echo "Headers:\n";
print_r($http_response_header);
echo "\nResponse:\n$result\n";
