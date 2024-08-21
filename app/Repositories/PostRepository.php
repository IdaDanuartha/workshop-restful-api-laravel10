<?php

namespace App\Repositories;

use App\Interfaces\PostRepositoryInterface;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

class PostRepository implements PostRepositoryInterface
{
   // Fungsi untuk mendapatkan semua data Post
   public function getAll(): Collection
   {
      // Mengambil semua post dengan relasi 'user' dan 'tags', lalu mengurutkan dari yang terbaru
      return Post::with(["user", "tags"])->latest()->get();
   }

   // Fungsi untuk mendapatkan data Post berdasarkan ID
   public function getById($id): Post
   {
      // Mencari post berdasarkan ID dengan relasi 'user' dan 'tags', atau gagal jika tidak ditemukan
      return Post::with(["user", "tags"])->findOrFail($id);
   }

   // Fungsi untuk menyimpan data Post baru
   public function store(array $data): Post
   {
      // Membuat post baru dengan data yang diberikan, kecuali 'tag_ids'
      $post = Post::create(Arr::except($data, "tag_ids"));
      // setelah menambah data post, lalu sekalian menambahkan list tag dari post tersebut
      $post->tags()->attach($data["tag_ids"]);
      // Mengambil post yang baru dibuat beserta relasinya untuk nanti dikirim ke client
      $get_post = $this->getById($post->id);

      // Mengembalikan post yang baru dibuat
      return $get_post;
   }

   // Fungsi untuk mengupdate data Post yang ada
   public function update(array $newData, Post $post): Post
   {
      // Mengupdate post berdasarkan ID dengan data baru yang diberikan
      $is_updated = $post->update($newData);
      // Mengganti list tag dari post tersebut dengan tag baru
      $post->tags()->sync($newData["tag_ids"]);
      // Mengambil post yang telah diperbarui
      $get_post = $this->getById($post->id);
      // Mengembalikan post yang diperbarui jika berhasil, atau null jika gagal
      return $is_updated ? $get_post : null;
   }

   // Fungsi untuk menghapus data Post
   public function delete($id): Post
   {
      // Mengambil post berdasarkan ID sebelum dihapus
      $get_post = $this->getById($id);
      // Menghapus post berdasarkan ID
      $is_deleted = Post::destroy($id);
      // Mengembalikan post yang dihapus jika berhasil, atau null jika gagal
      return $is_deleted ? $get_post : null;
   }
}

