<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$builder = \Illuminate\Support\Facades\DB::connection()->getSchemaBuilder();
$blueprint = new \Illuminate\Database\Schema\Blueprint('users');
$blueprint->enum('role', ['admin', 'customer']);
foreach ($blueprint->toSql(\Illuminate\Support\Facades\DB::connection(), new \Illuminate\Database\Schema\Grammars\PostgresGrammar()) as $sql) {
    echo $sql . "\n";
}
