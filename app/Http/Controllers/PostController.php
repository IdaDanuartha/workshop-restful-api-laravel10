<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Requests\Post\UploadImageRequest;
use App\Http\Resources\PostResource;
use App\Interfaces\PostRepositoryInterface;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PostController extends Controller
{
    // Constructor agar kita bisa menggunakan dependency injection untuk class PostRepositoryInterface
    public function __construct(
        private readonly PostRepositoryInterface $postRepositoryInterface
    ) {
    }

    /**
     * Menampilkan daftar resource (postingan).
     */
    public function index()
    {
        // Mengambil semua data postingan melalui repository
        $data = $this->postRepositoryInterface->getAll();

        // Mengirim respon sukses dengan data postingan
        return ApiResponse::sendResponse(PostResource::collection($data), 'Berhasil mengambil semua data postingan', 200);
    }

    /**
     * Menyimpan resource baru (postingan) ke dalam penyimpanan.
     */
    public function store(StorePostRequest $request)
    {
        // Memulai transaksi database
        DB::beginTransaction();
        try {
            // Memvalidasi request dan menyimpan data baru ke dalam variabel
            $newPost = $request->validated();
            // Mengatur slug berdasarkan judul postingan
            $newPost["slug"] = str()->slug($newPost["title"]);
            // Menyimpan ID pengguna yang sedang login
            $newPost["user_id"] = auth()->id();

            // Menyimpan postingan baru melalui repository
            $post = $this->postRepositoryInterface->store($newPost);

            // Komit transaksi jika berhasil
            DB::commit();
            // mengembalikan response "Postingan berhasil ditambahkan" beserta data postingan yang ditambahkan
            return ApiResponse::sendResponse(new PostResource($post), 'Postingan berhasil ditambahkan', 201);
        } catch (\Exception $ex) {
            // Rollback transaksi jika terjadi kesalahan
            return ApiResponse::rollback($ex);
        }
    }

    /**
     * Menampilkan resource yang ditentukan (postingan berdasarkan ID).
     */
    public function show($id)
    {
        try {
            // Mengambil data postingan berdasarkan ID melalui repository
            $post = $this->postRepositoryInterface->getById($id);

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
    public function update(UpdatePostRequest $request, Post $post)
    {
        // Memulai transaksi database
        DB::beginTransaction();
        try {
            // Memvalidasi request dan menyimpan data yang diupdate ke dalam variabel
            $updatePost = $request->validated();
            // Mengatur slug berdasarkan judul postingan
            $updatePost["slug"] = str()->slug($updatePost["title"]);

            // Memperbarui postingan melalui repository
            $post = $this->postRepositoryInterface->update($updatePost, $post);

            // Komit transaksi jika berhasil
            DB::commit();
            // mengembalikan response "Berhasil update postingan" beserta data postingan yang diupdate
            return ApiResponse::sendResponse($post, 'Berhasil update postingan', 200);
        } catch (\Exception $ex) {
            // Rollback transaksi jika terjadi kesalahan
            return ApiResponse::rollback($ex);
        }
    }

    /**
     * Mengunggah gambar untuk resource yang ditentukan (postingan).
     */
    public function uploadImage(UploadImageRequest $request, Post $post)
    {
        // Memulai transaksi database
        DB::beginTransaction();
        try {
            // Memvalidasi request dan menyimpan data yang diupdate ke dalam variabel
            $updatePost = $request->validated();
            // Jika ada gambar yang diunggah
            if ($request->image_path) {
                // Jika gambar lama ada, maka dihapus
                if (File::exists($post->image_path)) {
                    File::delete($post->image_path);
                }

                // Menyimpan gambar baru dengan nama unik
                $filename = 'uploads/posts/' . time() . '-' . $request->image_path->getClientOriginalName();
                $request->image_path->move('uploads/posts', $filename);
                $updatePost["image_path"] = $filename;
            }

            // Memperbarui postingan dengan gambar baru melalui repository
            $post = $this->postRepositoryInterface->update($updatePost, $post->id);

            // Komit transaksi jika berhasil
            DB::commit();
            // mengembalikan response "Berhasil update gambar postingan" beserta data gambar postingan yang diupdate
            return ApiResponse::sendResponse($post, 'Berhasil update gambar postingan', 200);
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
            // Menghapus postingan melalui repository
            $post = $this->postRepositoryInterface->delete($post->id);
            // Jika gambar ada, maka dihapus
            if (File::exists($post->image_path)) {
                File::delete($post->image_path);
            }

            // Komit transaksi jika berhasil
            DB::commit();
            // mengembalikan response "Berhasil menghapus postingan" beserta data postingan yang dihapus
            return ApiResponse::sendResponse($post, 'Berhasil menghapus postingan', 200);
        } catch (\Exception $ex) {
            // Rollback transaksi jika terjadi kesalahan
            return ApiResponse::rollback($ex);
        }
    }
}

