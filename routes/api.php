<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ProductionPlanController;
use App\Http\Controllers\API\ProductionOrderController;
use App\Http\Controllers\API\ProductionLogController;
use App\Http\Controllers\API\ProductionReportController;
use App\Http\Controllers\API\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// ========== AUTH ==========
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');



// ========== PRODUCTS ==========
Route::middleware('auth:sanctum')->group(function() {
    Route::apiResource('products', ProductController::class);
});

// ========== PRODUCTION PLANS ==========
Route::middleware('auth:sanctum')->group(function() {
    Route::get('/production_plans', [ProductionPlanController::class, 'index']);
    Route::post('/production_plans', [ProductionPlanController::class, 'store']);
    Route::get('/production_plans/report', [ProductionPlanController::class, 'report']);
    Route::get('/production_plans/{id}', [ProductionPlanController::class, 'show']);
    Route::put('/production_plans/{id}', [ProductionPlanController::class, 'update']);
    Route::delete('/production_plans/{id}', [ProductionPlanController::class, 'destroy']);
    Route::put('/production_plans/{id}/approve', [ProductionPlanController::class, 'approve']);
    Route::put('/production_plans/{id}/reject', [ProductionPlanController::class, 'reject']);
    Route::get('/production_plans/report', [ProductionPlanController::class, 'report']);

});

// ========== PRODUCTION ORDERS ==========
Route::middleware('auth:sanctum')->group(function() {
    Route::get('/production_orders', [ProductionOrderController::class, 'index']);
    Route::post('/production_orders', [ProductionOrderController::class, 'store']);
    Route::get('/production_orders/{id}', [ProductionOrderController::class, 'show']);
    Route::put('/production_orders/{id}', [ProductionOrderController::class, 'update']);
    Route::delete('/production_orders/{id}', [ProductionOrderController::class, 'destroy']);
    Route::put('/production_orders/{id}/status', [ProductionOrderController::class, 'updateStatus']);
    Route::get('/production_orders/{id}/logs', [ProductionOrderController::class, 'getLogs']);
    Route::get('/production-plans/{id}/has-orders', [ProductionPlanController::class, 'hasOrders']);
});

// ========== PRODUCTION LOGS ==========
Route::middleware('auth:sanctum')->group(function() {
    Route::apiResource('production_logs', ProductionLogController::class)->only(['index', 'store', 'show']);
});

// ========== PRODUCTION REPORTS ==========
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/reports', [ProductionReportController::class, 'index']);
    Route::get('/reports/{id}', [ProductionReportController::class, 'show']);
    Route::post('/reports', [ProductionReportController::class, 'store']);
    Route::put('/reports/{id}', [ProductionReportController::class, 'update']);
    Route::delete('/reports/{id}', [ProductionReportController::class, 'destroy']);
    Route::get('/reports/export', [ProductionReportController::class, 'export']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
