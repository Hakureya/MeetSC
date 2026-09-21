<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom konsumsi diubah dari ENUM menjadi string biasa supaya opsi terakhir
     * ("Other") dapat diisi manual oleh pemesan dengan teks bebas.
     * Daftar opsi baku tetap divalidasi di sisi controller (App\Support\KonsumsiOptions).
     */
    public function up(): void
    {
        Schema::table('room_reservations', function (Blueprint $table) {
            $table->string('konsumsi', 255)->default('Tanpa Konsumsi')->change();
        });
    }

    public function down(): void
    {
        Schema::table('room_reservations', function (Blueprint $table) {
            $table->enum('konsumsi', [
                'Snack',
                'Makan Siang',
                'Makan Malam',
                'Snack dan Makan Siang',
                'Tanpa Konsumsi',
                'Disiapkan PLN NP/IP/AP',
            ])->default('Tanpa Konsumsi')->change();
        });
    }
};
