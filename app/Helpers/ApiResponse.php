<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Exceptions\HttpResponseException;

class ApiResponse
{
    // Metode untuk melakukan rollback transaksi dan melempar pengecualian dengan pesan yang diberikan
    public static function rollback($e, $message = "Proses gagal dilakukan, sepertinya ada yang salah!")
    {
        // Melakukan rollback pada transaksi database
        DB::rollBack();
        // Memanggil metode throw untuk logging dan melempar pengecualian
        self::throw($e, $message);
    }

    // Metode untuk logging pengecualian dan melempar pengecualian HTTP dengan pesan yang diberikan
    public static function throw($e, $message = "Proses gagal dilakukan, sepertinya ada yang salah!")
    {
        // Mencatat log
        logger($e);
        // Melempar HttpResponseException dengan pesan JSON dan status kode 500 (Internal Server Error)
        throw new HttpResponseException(response()->json(["message" => $message], 500));
    }

    // Metode untuk mengirimkan respons API yang berhasil dengan data dan pesan yang diberikan
    public static function sendResponse($result, $message, $code = 200)
    {
        // Membuat array respons dengan kode status, message (jika ada), dan data hasil
        $response = [
            'success' => $code < 400,
            'message' => null,
            'data' => $result
        ];
        // Menambahkan message ke dalam respons jika ada
        if (!empty($message)) {
            $response['message'] = $message;
        }
        // Mengirimkan respons JSON dengan kode status yang diberikan
        return response()->json($response, $code);
    }
}

