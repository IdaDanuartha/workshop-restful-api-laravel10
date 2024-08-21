<?php

namespace App\Interfaces;

use App\Models\Post;

// Interface: Menyediakan kontrak atau blueprint yang harus diikuti oleh kelas yang mengimplementasikannya.
interface PostRepositoryInterface
{
    // Mendeklarasikan fungsi untuk mendapatkan semua data
    public function getAll();

    // Mendeklarasikan fungsi untuk mendapatkan data berdasarkan ID
    public function getById($id);

    // Mendeklarasikan fungsi untuk menyimpan data baru
    public function store(array $data);

    // Mendeklarasikan fungsi untuk memperbarui data berdasarkan ID
    public function update(array $newData, Post $post);

    // Mendeklarasikan fungsi untuk menghapus data berdasarkan ID
    public function delete($id);
}

