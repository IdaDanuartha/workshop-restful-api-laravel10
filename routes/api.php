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

    // Route untuk modul post (index, show)
    // Menggunakan apiResource untuk CRUD kecuali operasi store, update, dan delete
    Route::apiResource("posts", PostController::class)->except("store", "update", "delete");
    
    // Route untuk modul tag (index, show)
    // Menggunakan apiResource untuk CRUD kecuali operasi store, update, dan delete
    Route::apiResource("tags", TagController::class)->except("store", "update", "delete");
    
    // Mengelompokkan Routes yang memerlukan autentikasi menggunakan middleware auth:sanctum
    Route::middleware(["auth:sanctum"])->group(function() {
        // Route untuk modul post (create, update, delete)
        // Menggunakan apiResource untuk CRUD kecuali operasi index dan show
        Route::apiResource("posts", PostController::class)->except("index", "show");
        // Route untuk mengupload gambar pada post
        Route::post("posts/{post}/upload-image", [PostController::class, 'uploadImage']);
        // Route untuk modul tag (create, update, delete)
        // Menggunakan apiResource untuk CRUD kecuali operasi index dan show
        Route::middleware(['admin'])->apiResource("tags", TagController::class)->except("index", "show");
    });
});
