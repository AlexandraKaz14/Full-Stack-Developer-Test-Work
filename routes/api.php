<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/categories', function () {
    return \App\Models\Category::get();
})->name('home');

Route::apiResource('products', ProductController::class);

Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
