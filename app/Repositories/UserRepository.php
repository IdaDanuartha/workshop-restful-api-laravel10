<?php

namespace App\Repositories;

use App\Interfaces\UserRepositoryInterface;
use App\Models\Author;
use App\Models\User;
use Illuminate\Support\Arr;

class UserRepository implements UserRepositoryInterface
{
   // Fungsi untuk menyimpan data user dan author
   public function store(array $data): Author {
      // Membuat entitas User baru dari data yang diberikan, kecuali 'name'
      $user = User::create(Arr::except($data, "name"));
      // Menambahkan ID user yang baru dibuat ke data
      $data["user_id"] = $user->id;

      // Menambahkan Author baru dengan mengirimkan data name beserta user_id nya
      $newAuthor = Author::create(Arr::only($data, ["name", "user_id"]));

      // Mengambil Author yang baru dibuat beserta relasi User-nya
      $author = Author::with("user")->findOrFail($newAuthor->id);
      // Mengembalikan data Author yang baru dibuat
      return $author;
   }
}

