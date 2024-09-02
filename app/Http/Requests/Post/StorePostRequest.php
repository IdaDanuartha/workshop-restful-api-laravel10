<?php

namespace App\Http\Requests\Post;

use App\Helpers\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePostRequest extends FormRequest
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
            // Aturan untuk field "title": wajib diisi dan harus unik di tabel "posts" pada kolom "title"
            "title" => "required|unique:posts,title",
            // Aturan untuk field "content": wajib diisi dan minimal memiliki 15 karakter
            "content" => "required|min:15",
            // Aturan untuk field "status": wajib diisi dan hanya boleh bernilai "draft" atau "published"
            "status" => "required|in:draft,published",
            // Aturan untuk field "tag_ids": wajib diisi dan harus berupa array
            "tag_ids" => "required|array",
            // Aturan untuk setiap elemen dalam array "tag_ids": wajib diisi dan harus berupa integer
            "tag_ids.*" => "required|integer"
        ];
    }

    // Menangani validasi yang gagal
    public function failedValidation(Validator $validator)
    {
        // Melempar HttpResponseException dengan respons JSON yang berisi pesan error dan detail kesalahan validasi
        throw new HttpResponseException(ApiResponse::sendResponse($validator->errors(), "Validation errors", 400));
    }
}
