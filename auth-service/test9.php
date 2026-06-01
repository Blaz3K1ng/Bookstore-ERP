<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['jwt.secret' => null]);
try {
    \Tymon\JWTAuth\Facades\JWTAuth::attempt(['email'=>'admin@pageturn.com', 'password'=>'password123']);
} catch (\Exception $e) {
    echo get_class($e) . ': ' . $e->getMessage();
}
