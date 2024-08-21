<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    
    // Mengembalikan array aturan validasi untuk request ini
    public function rules(): array
    {
        return [
            // Aturan untuk field "name": wajib diisi
            "name" => "required",
            // Aturan untuk field "username": wajib diisi, hanya boleh mengandung huruf, angka, strip, dan garis bawah
            "username" => "required|alpha_dash",
            // Aturan untuk field "email": wajib diisi, harus berformat email, dan harus unik di tabel "users" pada kolom "email"
            "email" => "required|email|unique:users,email",
            // Aturan untuk field "password": wajib diisi dan minimal memiliki 6 karakter
            "password" => "required|min:6"
        ];
    }

    // Menangani validasi yang gagal
    public function failedValidation(Validator $validator)
    {
        // Melempar HttpResponseException dengan respons JSON yang berisi pesan error dan detail kesalahan validasi
        throw new HttpResponseException(response()->json([
            'success' => false, // Menandakan bahwa request tidak berhasil
            'message' => 'Validation errors', // Pesan umum untuk kesalahan validasi
            'data' => $validator->errors() // Detail kesalahan validasi
        ]));
    }
}
