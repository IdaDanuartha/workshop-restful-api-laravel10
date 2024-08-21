<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
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
            // Aturan untuk field "email": wajib diisi dan harus berformat email
            "email" => "required|email",
            // Aturan untuk field "password": wajib diisi
            "password" => "required"
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
