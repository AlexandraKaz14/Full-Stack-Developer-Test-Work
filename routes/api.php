<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/categories', function () {
    return \App\Models\Category::get();
})->name('home');

Route::apiResource('products', ProductController::class);

