<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('keperluan');
            $table->unsignedInteger('jumlah_peserta')->default(1);
            $table->enum('konsumsi', [
                'Snack',
                'Makan Siang',
                'Makan Malam',
                'Snack dan Makan Siang',
                'Tanpa Konsumsi',
                'Disiapkan PLN NP/IP/AP',
            ])->default('Tanpa Konsumsi');
            $table->enum('status', ['mendatang', 'selesai', 'menunggu_pembatalan', 'dibatalkan'])->default('mendatang');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_reservations');
    }
};
