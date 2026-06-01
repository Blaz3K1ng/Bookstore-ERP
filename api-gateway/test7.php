<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$req = \Illuminate\Http\Request::create('/api/v1/auth/login', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], '{"email":"admin@pageturn.com","password":"password123"}');
$req->setRouteResolver(function() use ($req) {
    $route = new \Illuminate\Routing\Route('POST', 'api/v1/auth/login', ['uses' => '\App\Http\Controllers\GatewayController@handle', 'service' => 'auth']);
    $route->bind($req);
    return $route;
});
putenv('AUTH_SERVICE_URL=https://httpbin.org/post');

$controller = new \App\Http\Controllers\GatewayController();
try {
    $response = $controller->handle($req);
    echo $response->getContent();
} catch (\Exception $e) {
    echo "EXCEPTION THROWN:\n";
    echo $e->getMessage();
}
