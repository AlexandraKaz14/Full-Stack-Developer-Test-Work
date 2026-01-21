<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Http\Request;

Route::get('/', function (Request $request) {
    return Inertia::render('Public/ProductsIndex', [
        'initialCategory' => $request->get('category_id'),
        'initialPage' => $request->get('page', 1),
        'initialSearch' => $request->get('search', '')
    ]);
});

Route::get('/login', fn () => Inertia::render('Auth/Login'));

Route::get('/admin/products', function () {
    return Inertia::render('Admin/ProductsIndex');
});

Route::get('/products/{id}', function ($id) {
    return Inertia::render('Public/ProductShow', ['id' => $id]);
})->name('products.show');



Route::get('/admin/products/create', fn () =>
Inertia::render('Admin/ProductForm')
);

Route::get('/admin/products/{id}/edit', function ($id) {
    return Inertia::render('Admin/ProductForm', [
        'id' => $id,
    ]);
});
