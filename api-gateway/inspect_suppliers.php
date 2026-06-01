<?php
$opts = [
    'http' => ['method' => 'POST', 'header' => "Content-Type: application/json\r\nAccept: application/json\r\n",
               'content' => json_encode(['email'=>'admin@pageturn.com','password'=>'password123']), 'ignore_errors' => true],
    'ssl' => ['verify_peer' => false]
];
$loginRes = json_decode(file_get_contents("https://bookstore-frontend-26nw.onrender.com/api/v1/auth/login", false, stream_context_create($opts)), true);
$token = $loginRes['token'];

$opts2 = ['http' => ['method' => 'GET', 'header' => "Authorization: Bearer $token\r\nAccept: application/json\r\n", 'ignore_errors' => true], 'ssl' => ['verify_peer' => false]];
$ctx = stream_context_create($opts2);

$sRes = file_get_contents("https://bookstore-frontend-26nw.onrender.com/api/v1/suppliers", false, $ctx);
echo "Suppliers response:\n$sRes\n\n";

$poRes = file_get_contents("https://bookstore-frontend-26nw.onrender.com/api/v1/purchase-orders", false, $ctx);
echo "Purchase Orders response:\n$poRes\n";
