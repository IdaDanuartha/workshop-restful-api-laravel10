<?php

namespace App\Providers;

use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\PostRepositoryInterface;
use App\Interfaces\TagRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\PostRepository;
use App\Repositories\TagRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */

    // Fungsi register ini digunakan untuk mengikat interface ke implementasi yang spesifik dalam konteks Dependency Injection (DI) di framework Laravel.
    // Ketika sebuah objek memerlukan object tersebut, Laravel akan memberikan instance dari object yang terkait.
    public function register(): void
    {
        // Mendaftarkan PostRepositoryInterface untuk diimplementasikan oleh PostRepository
        $this->app->bind(PostRepositoryInterface::class, PostRepository::class);
    
        // Mendaftarkan TagRepositoryInterface untuk diimplementasikan oleh TagRepository
        $this->app->bind(TagRepositoryInterface::class, TagRepository::class);
    
        // Mendaftarkan UserRepositoryInterface untuk diimplementasikan oleh UserRepository
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    }
    

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
