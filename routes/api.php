<?php

use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\CategoryController;
use Illuminate\Support\Facades\Route;

// route khusus harus di atas apiResource agar tidak dibaca sebagai {book}
Route::get('statistics', [BookController::class, 'statistics']);
Route::get('books/low-stock', [BookController::class, 'lowStock']);

Route::apiResource('authors', AuthorController::class);
Route::apiResource('categories', CategoryController::class)->only(['index', 'store', 'destroy']);
Route::apiResource('books', BookController::class);