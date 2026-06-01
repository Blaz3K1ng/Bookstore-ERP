<?php
$dsn = "pgsql:host=ep-young-water-ao25ofwu-pooler.c-2.ap-southeast-1.aws.neon.tech;dbname=neondb;sslmode=require";
try {
    $pdo = new PDO($dsn, "neondb_owner", "npg_iwQhaUR7tVu3", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]);
    echo "Connected successfully\n";
} catch (\PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
