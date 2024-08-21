<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TagController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Menambahkan prefix pada semua Route (misalnya: /api/v1/posts)
Route::prefix("v1")->group(function() {
    // Mengelompokkan Routes yang menggunakan AuthController
    Route::controller(AuthController::class)->group(function() {
        // Route untuk mengakses halaman login jika pengguna belum login
        Route::get("/user-not-logged-in", "userNotLoggedIn")->name("login");
        // Route untuk proses login
        Route::post("/login", "login");
        // Route untuk proses pendaftaran (registrasi)
        Route::post("/register", "register");
    });

    // Menggunakan apiResource untuk CRUD posts
    Route::apiResource("posts", PostController::class);
    // Route untuk mengupload gambar pada post
    Route::post("posts/{post}/upload-image", [PostController::class, 'uploadImage']);

    // Menggunakan apiResource untuk CRUD tags
    Route::apiResource("tags", TagController::class);
});
