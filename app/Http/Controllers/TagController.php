<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\Tag\StoreTagRequest;
use App\Http\Requests\Tag\UpdateTagRequest;
use App\Http\Resources\TagResource;
use App\Interfaces\TagRepositoryInterface;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;

class TagController extends Controller
{
    // Constructor agar kita bisa menggunakan dependency injection untuk class TagRepositoryInterface
    public function __construct(
        private readonly TagRepositoryInterface $tagRepositoryInterface
    ) {
    }

    /**
     * Menampilkan daftar resource (tag).
     */
    public function index()
    {
        // Mengambil semua data tag melalui repository
        $data = $this->tagRepositoryInterface->getAll();
        
        // Mengirim respon sukses dengan data tag
        return ApiResponse::sendResponse(TagResource::collection($data), 'Berhasil mengambil semua data tag', 200);
    }

    /**
     * Menyimpan resource baru (tag) ke dalam penyimpanan.
     */
    public function store(StoreTagRequest $request)
    {
        // Memulai transaksi database
        DB::beginTransaction();
        try {
            // Memvalidasi request dan menyimpan data baru ke dalam variabel
            $newTag = $request->validated();
            // Mengatur slug berdasarkan nama tag
            $newTag["slug"] = str()->slug($newTag["name"]);

            // Menyimpan tag baru melalui repository
            $tag = $this->tagRepositoryInterface->store($newTag);

            // Komit transaksi jika berhasil
            DB::commit();
            // mengembalikan response "Tag berhasil ditambahkan" beserta data tag yang ditambahkan
            return ApiResponse::sendResponse(new TagResource($tag), 'Tag berhasil ditambahkan', 201);
        } catch (\Exception $ex) {
            // Rollback transaksi jika terjadi kesalahan
            return ApiResponse::rollback($ex);
        }
    }

    /**
     * Menampilkan resource yang ditentukan (tag berdasarkan ID).
     */
    public function show($id)
    {
        try {
            // Mengambil data tag berdasarkan ID melalui repository
            $tag = $this->tagRepositoryInterface->getById($id);

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
            // Memvalidasi request dan menyimpan data yang diupdate ke dalam variabel
            $updateTag = $request->validated();
            // Mengatur slug berdasarkan nama tag
            $updateTag["slug"] = str()->slug($updateTag["name"]);

            // Memperbarui tag melalui repository
            $tag = $this->tagRepositoryInterface->update($updateTag, $tag->id);

            // Komit transaksi jika berhasil
            DB::commit();
            // mengembalikan response "berhasil update tag" beserta data tag yang diupdate
            return ApiResponse::sendResponse($tag, 'Berhasil update tag', 200);
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
            // Menghapus tag melalui repository
            $tag = $this->tagRepositoryInterface->delete($tag->id);

            // Komit transaksi jika berhasil
            DB::commit();
            // mengembalikan response "berhasil menghapus tag" beserta data tag yang dihapus
            return ApiResponse::sendResponse($tag, 'Berhasil menghapus tag', 200);
        } catch (\Exception $ex) {
            // Rollback transaksi jika terjadi kesalahan
            return ApiResponse::rollback($ex);
        }
    }
}
