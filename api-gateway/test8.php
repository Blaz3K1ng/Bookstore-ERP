<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $res = \Illuminate\Support\Facades\Http::send('POST', 'https://httpbin.org/post', [
        'json' => ['test' => 'data'],
        'form_params' => null
    ]);
    echo $res->status();
} catch (\Exception $e) {
    echo "EXCEPTION THROWN:\n";
    echo $e->getMessage();
}
