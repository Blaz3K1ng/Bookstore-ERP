<?php
$opts = ['http' => ['method' => 'GET', 'ignore_errors' => true], 'ssl' => ['verify_peer' => false]];
$ctx = stream_context_create($opts);

// Check gateway health
$r = file_get_contents('https://bookstore-erp.onrender.com/api/health', false, $ctx);
echo "Gateway health: $r\n";

// Check what the root is
$r2 = file_get_contents('https://bookstore-erp.onrender.com/', false, $ctx);
echo "Root: " . substr($r2, 0, 200) . "\n\n";

// Check the frontend (if separate)
$r3 = file_get_contents('https://bookstore-frontend-26nw.onrender.com/api/v1/books', false, $ctx);
echo "Frontend books: " . substr($r3, 0, 200) . "\n";
