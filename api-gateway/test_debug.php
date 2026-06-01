<?php
$opts = [
    'http' => [
        'method' => 'GET',
        'ignore_errors' => true
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false
    ]
];
$context = stream_context_create($opts);
$result = file_get_contents('https://bookstore-erp.onrender.com/api/debug', false, $context);
echo "Response:\n$result\n";
