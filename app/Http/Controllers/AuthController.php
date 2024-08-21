<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\AuthResource;
use App\Interfaces\UserRepositoryInterface;
use App\Models\Author;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    // Metode untuk melakukan login
    public function login(Request $request)
    {
        try {
            $validatedData = $request->validate([
                // Aturan untuk field "email": wajib diisi dan harus berformat email
                "email" => "required|email",
                // Aturan untuk field "password": wajib diisi
                "password" => "required"
            ]);

            // Memeriksa kredensial pengguna menggunakan email dan password
            if(Auth::attempt(['email' => $validatedData["email"], 'password' => $validatedData["password"]])){ 
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
        } catch (\Illuminate\Validation\ValidationException $ex) {
            // Menangani kesalahan validasi
            throw new HttpResponseException(response()->json([
                'success' => false, // Menandakan bahwa request tidak berhasil
                'message' => 'Validation errors', // Pesan umum untuk kesalahan validasi
                'data' => $ex->errors() // Detail kesalahan validasi
            ]));
        } catch(\Exception $ex){
            // Mengembalikan respons rollback jika terjadi kesalahan
            return ApiResponse::rollback($ex);
        }
    }

    // Metode untuk registrasi pengguna baru
    public function register(Request $request)
    {
        // Memulai transaksi database
        DB::beginTransaction();

        try{
            $validatedData = $request->validate([
                // Aturan untuk field "name": wajib diisi
                "name" => "required",
                // Aturan untuk field "username": wajib diisi, hanya boleh mengandung huruf, angka, strip, dan garis bawah
                "username" => "required|alpha_dash",
                // Aturan untuk field "email": wajib diisi, harus berformat email, dan harus unik di tabel "users" pada kolom "email"
                "email" => "required|email|unique:users,email",
                // Aturan untuk field "password": wajib diisi dan minimal memiliki 6 karakter
                "password" => "required|min:6"
            ]);

            // Membuat entitas User baru dari data yang diberikan, kecuali 'name'
            $user = User::create(Arr::except($validatedData, "name"));
            // Menambahkan ID user yang baru dibuat ke data
            $validatedData["user_id"] = $user->id;

            // Menambahkan Author baru dengan mengirimkan data name beserta user_id nya
            $newAuthor = Author::create(Arr::only($validatedData, ["name", "user_id"]));

            // Mengambil Author yang baru dibuat beserta relasi User-nya
            $author = Author::with("user")->findOrFail($newAuthor->id);

            // Melakukan commit pada transaksi jika berhasil
            DB::commit();
            return ApiResponse::sendResponse(new AuthResource($author),'Registrasi berhasil', 201);

        } catch (\Illuminate\Validation\ValidationException $ex) {
            // Menangani kesalahan validasi
            throw new HttpResponseException(response()->json([
                'success' => false, // Menandakan bahwa request tidak berhasil
                'message' => 'Validation errors', // Pesan umum untuk kesalahan validasi
                'data' => $ex->errors() // Detail kesalahan validasi
            ]));
        } catch(\Exception $ex){
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

