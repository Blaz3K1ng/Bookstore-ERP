<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$user = \App\Models\User::where('email', 'admin@pageturn.com')->first();
if ($user) {
    echo "USER EXISTS! Password hash: " . $user->password . "\n";
    if (\Illuminate\Support\Facades\Hash::check('password123', $user->password)) {
        echo "MATCHES password123!\n";
    } else {
        echo "DOES NOT MATCH password123!\n";
    }
} else {
    echo "USER DOES NOT EXIST!\n";
}
