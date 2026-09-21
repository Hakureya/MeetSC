<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel zoom_reservations awalnya cuma punya status: mendatang, selesai, dibatalkan.
     * Padahal alur "ajukan pembatalan" (lihat DashboardController::requestCancel) butuh
     * status menunggu_pembatalan juga, sama seperti room_reservations. Ditambahkan di sini
     * supaya reservasi zoom bisa diajukan pembatalan tanpa error enum.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE zoom_reservations MODIFY status ENUM('mendatang', 'selesai', 'menunggu_pembatalan', 'dibatalkan') NOT NULL DEFAULT 'mendatang'");
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE zoom_reservations MODIFY status ENUM('mendatang', 'selesai', 'dibatalkan') NOT NULL DEFAULT 'mendatang'");
        }
    }
};
