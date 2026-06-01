<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$user = new \App\Models\User();
$user->password = \Illuminate\Support\Facades\Hash::make('password123');

if (\Illuminate\Support\Facades\Hash::check('password123', $user->password)) {
    echo "MATCHES!";
} else {
    echo "DOES NOT MATCH!";
}
