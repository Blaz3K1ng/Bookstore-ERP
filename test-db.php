<?php
$url = "postgresql://neondb_owner:npg_iwQhaUR7tVu3@ep-young-water-ao25ofwu-pooler.c-2.ap-southeast-1.aws.neon.tech/neondb?sslmode=require";
$url=preg_replace("#^postgresql://#","postgres://",$url);
$parts=parse_url($url);
$dsn = "pgsql:host=" . $parts["host"] . ";dbname=" . ltrim($parts["path"], "/") . ";sslmode=require";
try {
    $pdo = new PDO($dsn, $parts["user"], $parts["pass"], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "Connected!";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
