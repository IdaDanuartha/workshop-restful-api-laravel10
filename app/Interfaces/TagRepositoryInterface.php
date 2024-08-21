<?php

namespace App\Interfaces;

// Interface: Menyediakan kontrak atau blueprint yang harus diikuti oleh kelas yang mengimplementasikannya.
interface TagRepositoryInterface
{
    // Mendeklarasikan fungsi untuk mendapatkan semua tag
    public function getAll();

    // Mendeklarasikan fungsi untuk mendapatkan tag berdasarkan ID
    public function getById($id);

    // Mendeklarasikan fungsi untuk menyimpan tag baru
    public function store(array $data);

    // Mendeklarasikan fungsi untuk memperbarui tag berdasarkan ID
    public function update(array $data, $id);

    // Mendeklarasikan fungsi untuk menghapus tag berdasarkan ID
    public function delete($id);
}

