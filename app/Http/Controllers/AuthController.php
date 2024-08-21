<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\AuthResource;
use App\Interfaces\UserRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    // Konstruktor untuk menginisialisasi UserRepositoryInterface yang hanya bisa dibaca
    public function __construct(
        private readonly UserRepositoryInterface $userRepositoryInterface
    ) {}

    // Metode untuk melakukan login
    public function login(LoginRequest $request)
    {
        try {
            // Memeriksa kredensial pengguna menggunakan email dan password
            if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
                $user = Auth::user(); 
                $token =  $user->createToken('authToken')->plainTextToken; 
                
                // Mengembalikan respons API jika login berhasil
                return ApiResponse::sendResponse(new AuthResource([
                    "user" => $user,
                    "token" => $token,
                    "token_type" => "Bearer",
                ]),'Login berhasil', 200);
            } else {
                // Mengembalikan respons API jika email atau password salah
                return ApiResponse::sendResponse(null,"Email atau password salah", 400);
            }
        } catch(\Exception $ex){
            // Mengembalikan respons rollback jika terjadi kesalahan
            return ApiResponse::rollback($ex);
        }
    }

    // Metode untuk registrasi pengguna baru
    public function register(RegisterRequest $request)
    {
        // Memulai transaksi database
        DB::beginTransaction();
        try{
            // Menyimpan data pengguna baru melalui UserRepositoryInterface
            $user = $this->userRepositoryInterface->store($request->validated());

            // Melakukan commit pada transaksi jika berhasil
            DB::commit();
            return ApiResponse::sendResponse(new AuthResource($user),'Registrasi berhasil', 201);

        }catch(\Exception $ex){
            // Mengembalikan respons rollback jika terjadi kesalahan
            return ApiResponse::rollback($ex);
        }
    }

    // Metode untuk menangani pengguna yang tidak login
    public function userNotLoggedIn()
    {
        // Mengembalikan respons API jika pengguna tidak login
        return ApiResponse::sendResponse(null, "Akses anda ditolak! Anda belum melakukan login", 401);
    }
}

