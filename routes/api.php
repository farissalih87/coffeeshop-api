<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ── Public (Customer) Routes ────────────────────────────────────────────────

// Auth
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:sanctum');
});

// Menu (public - customers can browse)
Route::prefix('menu')->group(function () {
    Route::get('categories', [MenuController::class, 'categories']);
    Route::get('items', [MenuController::class, 'items']);
});

// Customer order placement (no auth required)
Route::post('orders', [OrderController::class, 'store']);

// ── Staff Routes (auth required) ────────────────────────────────────────────

Route::middleware('auth:sanctum')->prefix('staff')->group(function () {
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus']);
});

// ── Admin Routes (auth + admin role required) ────────────────────────────────

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    // Stats & Reports
    Route::get('stats', [AdminController::class, 'stats']);
    Route::get('reports', [AdminController::class, 'reports']);

    // Menu Management
    Route::apiResource('categories', MenuController::class)->only(['store','update','destroy']);
    Route::prefix('items')->group(function () {
        Route::post('/', [MenuController::class, 'storeItem']);
        Route::post('{item}', [MenuController::class, 'updateItem']); // POST for multipart
        Route::delete('{item}', [MenuController::class, 'destroyItem']);
    });

    // Staff Management
    Route::get('users', [AdminController::class, 'users']);
    Route::post('users', [AdminController::class, 'createUser']);
    Route::put('users/{user}', [AdminController::class, 'updateUser']);
    Route::delete('users/{user}', [AdminController::class, 'deleteUser']);
});
