<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\Tag\StoreTagRequest;
use App\Http\Requests\Tag\UpdateTagRequest;
use App\Http\Resources\TagResource;
use App\Interfaces\TagRepositoryInterface;
use App\Models\Tag;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TagController extends Controller
{
    /**
     * Menampilkan daftar resource (tag).
     */
    public function index()
    {
        // Mengambil semua tag dan mengurutkannya dari yang terbaru
        $tags = Tag::latest()->get();
        
        // Mengirim respon sukses dengan data tag
        return ApiResponse::sendResponse(TagResource::collection($tags), 'Berhasil mengambil semua data tag', 200);
    }

    /**
     * Menyimpan resource baru (tag) ke dalam penyimpanan.
     */
    public function store(Request $request)
    {
        // Memulai transaksi database
        DB::beginTransaction();
        try {
            $validatedData = $request->validate([
                // Aturan untuk field "name":
                // - wajib diisi
                // - harus unik di tabel "tags" pada kolom "name"
                "name" => "required|unique:tags,name",
            ]);

            // Memvalidasi request dan menyimpan data baru ke dalam variabel
            $newTag = $validatedData;
            // Mengatur slug berdasarkan nama tag
            $newTag["slug"] = str()->slug($newTag["name"]);

            // Membuat tag baru dengan data yang diberikan
            $tag = Tag::create($newTag);

            // Komit transaksi jika berhasil
            DB::commit();
            // mengembalikan response "Tag berhasil ditambahkan" beserta data tag yang ditambahkan
            return ApiResponse::sendResponse(new TagResource($tag), 'Tag berhasil ditambahkan', 201);
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
     * Menampilkan resource yang ditentukan (tag berdasarkan ID).
     */
    public function show(Tag $tag)
    {
        try {
            // Mengambil data tag berdasarkan ID
            $tag = Tag::findOrFail($tag->id);

            // Mengirim respon sukses dengan data tag
            return ApiResponse::sendResponse(new TagResource($tag), 'Berhasil mengambil detail tag', 200);
        } catch (\Exception $ex) {
            // Mengirim respon error jika terjadi kesalahan
            return ApiResponse::sendResponse($ex, 'Tag tidak ditemukan', 400);
        }
    }

    /**
     * Memperbarui resource yang ditentukan (tag berdasarkan ID).
     */
    public function update(UpdateTagRequest $request, Tag $tag)
    {
        // Memulai transaksi database
        DB::beginTransaction();
        try {
            $validatedData = $request->validate([
                // Aturan untuk field "name":
                // - wajib diisi
                // - harus unik di tabel "tags" pada kolom "name", kecuali untuk tag dengan ID yang sedang diedit
                //   (pengecualian ini diperlukan agar tidak terjadi kesalahan validasi saat memperbarui tag dengan nama yang sama)
                "name" => "required|unique:tags,name," . $tag->id,
            ]);

            // Memvalidasi request dan menyimpan data yang diupdate ke dalam variabel
            $updateTag = $validatedData;
            // Mengatur slug berdasarkan nama tag
            $updateTag["slug"] = str()->slug($updateTag["name"]);

            // Mengupdate tag berdasarkan ID dengan data baru yang diberikan
            $tag->update($updateTag);

            // Mengambil data tag berdasarkan ID
            $getTag = Tag::findOrFail($tag->id);

            // Komit transaksi jika berhasil
            DB::commit();
            // mengembalikan response "berhasil update tag" beserta data tag yang diupdate
            return ApiResponse::sendResponse($getTag, 'Berhasil update tag', 200);
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
     * Menghapus resource yang ditentukan (tag).
     */
    public function destroy(Tag $tag)
    {
        // Memulai transaksi database
        DB::beginTransaction();
        try {
            // Mengambil data tag berdasarkan ID
            $getTag = Tag::findOrFail($tag->id);

            // Menghapus tag berdasarkan ID yang dikirimkan dari client
            $tag->delete();

            // Komit transaksi jika berhasil
            DB::commit();
            // mengembalikan response "berhasil menghapus tag" beserta data tag yang dihapus
            return ApiResponse::sendResponse($getTag, 'Berhasil menghapus tag', 200);
        } catch (\Exception $ex) {
            // Rollback transaksi jika terjadi kesalahan
            return ApiResponse::rollback($ex);
        }
    }
}
