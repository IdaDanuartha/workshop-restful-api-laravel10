<?php

namespace App\Interfaces;

// Interface: Menyediakan kontrak atau blueprint yang harus diikuti oleh kelas yang mengimplementasikannya.
interface UserRepositoryInterface
{
    // Mendeklarasikan fungsi untuk menyimpan user baru
    public function store(array $data);
}
