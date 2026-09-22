<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Di sini kita mendaftarkan semua event broadcast channel milik aplikasi.
| Channel yang terdaftar otomatis dipetakan ke channel di server WebSocket
| (Reverb), sehingga klien dapat memakainya lewat Laravel Echo.
|
*/

/**
 * Channel private "zoom-schedule": disiarkan setiap kali data reservasi
 * breakout room Zoom berubah (dibuat / diajukan pembatalan / dibatalkan admin),
 * supaya tabel jadwal di halaman "Breakout Room Zoom" (resources/views/schedule/index.blade.php)
 * ter-update secara real time untuk semua pengguna yang sedang membukanya.
 *
 * Datanya sendiri (siapa memesan ruang apa) memang sudah bisa dilihat oleh semua
 * pengguna yang login lewat endpoint /link-zoom/data, jadi otorisasi di sini cukup
 * memastikan yang mendengarkan adalah pengguna yang sudah login.
 */
Broadcast::channel('zoom-schedule', function ($user) {
    return true;
});
