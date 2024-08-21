<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Requests\Post\UploadImageRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PostController extends Controller
{
    /**
     * Menampilkan daftar resource (postingan).
     */
    public function index()
    {
        // Mengambil semua post dengan relasi 'user' dan 'tags', lalu mengurutkan dari yang terbaru
        $posts = Post::with(["user", "tags"])->latest()->get();

        // Mengirim respon sukses dengan data postingan
        return ApiResponse::sendResponse(PostResource::collection($posts), 'Berhasil mengambil semua data postingan', 200);
    }

    /**
     * Menyimpan resource baru (postingan) ke dalam penyimpanan.
     */
    public function store(Request $request)
    {
        // Memulai transaksi database
        DB::beginTransaction();
        try {
            $validatedData = $request->validate([
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
            ]);
            
            // Memvalidasi request dan menyimpan data baru ke dalam variabel
            $newPost = $validatedData;

            // Mengatur slug berdasarkan judul postingan
            $newPost["slug"] = str()->slug($newPost["title"]);
            // Menyimpan ID pengguna yang sedang login
            $newPost["user_id"] = auth('api')->id();

            // Membuat post baru dengan data yang diberikan, kecuali 'tag_ids'
            $post = Post::create(Arr::except($newPost, "tag_ids"));
            // setelah menambah data post, lalu sekalian menambahkan list tag dari post tersebut
            $post->tags()->attach($newPost["tag_ids"]);
            // Mengambil post yang baru dibuat beserta relasinya untuk nanti dikirim ke client
            $getPost = Post::with(["user", "tags"])->findOrFail($post->id);

            // Komit transaksi jika berhasil
            DB::commit();
            // mengembalikan response "Postingan berhasil ditambahkan" beserta data postingan yang ditambahkan
            return ApiResponse::sendResponse(new PostResource($getPost), 'Postingan berhasil ditambahkan', 201);
        } catch (\Illuminate\Validation\ValidationException $ex) {
            // Menangani kesalahan validasi
            throw new HttpResponseException(response()->json([
                'success' => false, // Menandakan bahwa request tidak berhasil
                'message' => 'Validation errors', // Pesan umum untuk kesalahan validasi
                'data' => $ex->errors() // Detail kesalahan validasi
            ]));
        } catch (\Exception $ex) {
            // Rollback transaksi jika terjadi kesalahan
            return ApiResponse::rollback($ex);
        }
    }

    /**
     * Menampilkan resource yang ditentukan (postingan berdasarkan ID).
     */
    public function show(Post $post)
    {
        try {
            // Mencari post berdasarkan ID dengan relasi 'user' dan 'tags', atau gagal jika tidak ditemukan
            $post = Post::with(["user", "tags"])->findOrFail($post->id);

            // Mengirim respon sukses dengan data postingan
            return ApiResponse::sendResponse(new PostResource($post), 'Berhasil mengambil detail postingan', 200);
        } catch (\Exception $ex) {
            // Mengirim respon error jika terjadi kesalahan
            return ApiResponse::sendResponse($ex, 'Postingan tidak ditemukan', 400);
        }
    }

    /**
     * Memperbarui resource yang ditentukan (postingan berdasarkan ID).
     */
    public function update(Request $request, Post $post)
    {
        // Memulai transaksi database
        DB::beginTransaction();
        try {
            $validatedData = $request->validate([
                // Aturan untuk field "title": wajib diisi dan harus unik di tabel "posts" pada kolom "title"
                "title" => "required|unique:posts,title," . $post->id,
                // Aturan untuk field "content": wajib diisi dan minimal memiliki 15 karakter
                "content" => "required|min:15",
                // Aturan untuk field "status": wajib diisi dan hanya boleh bernilai "draft" atau "published"
                "status" => "required|in:draft,published",
                // Aturan untuk field "tag_ids": wajib diisi dan harus berupa array
                "tag_ids" => "required|array",
                // Aturan untuk setiap elemen dalam array "tag_ids": wajib diisi dan harus berupa integer
                "tag_ids.*" => "required|integer"
            ]);

            // Memvalidasi request dan menyimpan data yang diupdate ke dalam variabel
            $updatePost = $validatedData;
            // Mengatur slug berdasarkan judul postingan
            $updatePost["slug"] = str()->slug($updatePost["title"]);


            // Mengupdate post berdasarkan ID yang dikirimkan dari client
            $post->update($updatePost);

            // Mengganti list tag dari post tersebut dengan tag baru
            $post->tags()->sync($validatedData["tag_ids"]);

            // Mengambil post yang baru dibuat beserta relasinya untuk nanti dikirim ke client
            $getPost = Post::with(["user", "tags"])->findOrFail($post->id);

            // Komit transaksi jika berhasil
            DB::commit();
            // mengembalikan response "Berhasil update postingan" beserta data postingan yang diupdate
            return ApiResponse::sendResponse($getPost, 'Berhasil update postingan', 200);
        } catch (\Illuminate\Validation\ValidationException $ex) {
            // Menangani kesalahan validasi
            throw new HttpResponseException(response()->json([
                'success' => false, // Menandakan bahwa request tidak berhasil
                'message' => 'Validation errors', // Pesan umum untuk kesalahan validasi
                'data' => $ex->errors() // Detail kesalahan validasi
            ]));
        } catch (\Exception $ex) {
            // Rollback transaksi jika terjadi kesalahan
            return ApiResponse::rollback($ex);
        }
    }

    /**
     * Mengunggah gambar untuk resource yang ditentukan (postingan).
     */
    public function uploadImage(Request $request, Post $post)
    {
        // Memulai transaksi database
        DB::beginTransaction();
        try {
            $validatedData = $request->validate([
                // Aturan untuk field "image_path": 
                // - wajib diisi
                // - harus berupa file gambar
                // - ukuran maksimal 5000 KB (5 MB)
                // - hanya boleh berformat file png, jpg, jpeg, webp, atau svg
                "image_path" => "required|image|max:5000|mimes:png,jpg,jpeg,webp,svg",
            ]);
            
            // Jika ada gambar yang diunggah
            if ($request->image_path) {
                // Jika gambar lama ada, maka dihapus
                if (File::exists($post->image_path)) {
                    File::delete($post->image_path);
                }

                // Menyimpan gambar baru dengan nama unik
                $filename = 'uploads/posts/' . time() . '-' . $request->image_path->getClientOriginalName();

                // Cara pertama untuk upload gambar (tersimpan di folder public langsung)
                $request->image_path->move('uploads/posts', $filename);

                // Cara kedua untuk upload gambar (tersimpan di folder storage)
                // $request->image_path->storeAs('/', $filename);
                
                $validatedData["image_path"] = $filename;
            }

            // Mengupdate post berdasarkan ID yang dikirimkan dari client
             $post->update($validatedData);

            // Mengambil post yang baru dibuat beserta relasinya untuk nanti dikirim ke client
            $getPost = Post::with(["user", "tags"])->findOrFail($post->id);

            // Komit transaksi jika berhasil
            DB::commit();
            // mengembalikan response "Berhasil update gambar postingan" beserta data gambar postingan yang diupdate
            return ApiResponse::sendResponse($getPost, 'Berhasil update gambar postingan', 200);
        } catch (\Illuminate\Validation\ValidationException $ex) {
            // Menangani kesalahan validasi
            throw new HttpResponseException(response()->json([
                'success' => false, // Menandakan bahwa request tidak berhasil
                'message' => 'Validation errors', // Pesan umum untuk kesalahan validasi
                'data' => $ex->errors() // Detail kesalahan validasi
            ]));
        } catch (\Exception $ex) {
            // Rollback transaksi jika terjadi kesalahan
            return ApiResponse::rollback($ex);
        }
    }

    /**
     * Menghapus resource yang ditentukan (postingan).
     */
    public function destroy(Post $post)
    {
        // Memulai transaksi database
        DB::beginTransaction();
        try {
            // Mengambil post berdasarkan ID sebelum dihapus
            $getPost = Post::with(["user", "tags"])->findOrFail($post->id);
            // Menghapus post berdasarkan ID yang dikirimkan dari client
            $post->delete();

            // Jika gambar ada, maka dihapus
            if (File::exists($post->image_path)) {
                File::delete($post->image_path);
            }

            // Komit transaksi jika berhasil
            DB::commit();
            // mengembalikan response "Berhasil menghapus postingan" beserta data postingan yang dihapus
            return ApiResponse::sendResponse($getPost, 'Berhasil menghapus postingan', 200);
        } catch (\Exception $ex) {
            // Rollback transaksi jika terjadi kesalahan
            return ApiResponse::rollback($ex);
        }
    }
}

