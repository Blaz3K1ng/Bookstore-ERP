<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SupplierController;
use App\Http\Middleware\VerifyJwtToken;
use Illuminate\Support\Facades\Route;

Route::get('health', HealthController::class);

Route::prefix('v1')->middleware(VerifyJwtToken::class)->group(function () {
    // Supplier management (admin + inventory_admin)
    Route::get('suppliers',        [SupplierController::class, 'index'])
         ->middleware('role:admin,super_admin,inventory_admin');
    Route::post('suppliers',       [SupplierController::class, 'store'])
         ->middleware('role:admin,super_admin,inventory_admin');
    Route::get('suppliers/{id}',   [SupplierController::class, 'show'])
         ->middleware('role:admin,super_admin,inventory_admin');
    Route::patch('suppliers/{id}', [SupplierController::class, 'update'])
         ->middleware('role:admin,super_admin,inventory_admin');
    Route::delete('suppliers/{id}',[SupplierController::class, 'destroy'])
         ->middleware('role:admin,super_admin,inventory_admin');

    // Purchase orders (admin + inventory_admin)
    Route::get('purchase-orders',               [PurchaseOrderController::class, 'index'])
         ->middleware('role:admin,super_admin,inventory_admin');
    Route::post('purchase-orders',              [PurchaseOrderController::class, 'store'])
         ->middleware('role:admin,super_admin,inventory_admin');
    Route::get('purchase-orders/{id}',          [PurchaseOrderController::class, 'show'])
         ->middleware('role:admin,super_admin,inventory_admin');
    Route::patch('purchase-orders/{id}/status', [PurchaseOrderController::class, 'updateStatus'])
         ->middleware('role:admin,super_admin,inventory_admin');
});
