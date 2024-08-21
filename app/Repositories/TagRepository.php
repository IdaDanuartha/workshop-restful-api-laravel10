<?php

namespace App\Repositories;

use App\Interfaces\TagRepositoryInterface;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

class TagRepository implements TagRepositoryInterface
{
   // Fungsi untuk mendapatkan semua data Tag
   public function getAll(): Collection
   {
      // Mengambil semua tag dan mengurutkannya dari yang terbaru
      return Tag::latest()->get();
   }

   // Fungsi untuk mendapatkan data Tag berdasarkan ID
   public function getById($id): Tag
   {
      // Mencari tag berdasarkan ID, atau gagal jika tidak ditemukan
      return Tag::findOrFail($id);
   }

   // Fungsi untuk menyimpan data Tag baru
   public function store(array $data): Tag
   {
      // Membuat tag baru dengan data yang diberikan
      return Tag::create($data);
   }

   // Fungsi untuk mengupdate data Tag yang ada
   public function update(array $newData, $id): Tag
   {
      // Mengupdate tag berdasarkan ID dengan data baru yang diberikan
      $is_updated = Tag::whereId($id)->update($newData);
      // Mengambil tag yang telah diperbarui
      $get_tag = $this->getById($id);
      // Mengembalikan tag yang diperbarui jika berhasil, atau null jika gagal
      return $is_updated ? $get_tag : null;
   }

   // Fungsi untuk menghapus data Tag
   public function delete($id): Tag
   {
      // Mengambil tag berdasarkan ID sebelum dihapus
      $get_tag = $this->getById($id);
      // Menghapus tag berdasarkan ID
      $is_deleted = Tag::destroy($id);
      // Mengembalikan tag yang dihapus jika berhasil, atau null jika gagal
      return $is_deleted ? $get_tag : null;
   }
}

