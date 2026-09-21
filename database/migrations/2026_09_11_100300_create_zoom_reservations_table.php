<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zoom_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nama_agenda');
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->unsignedTinyInteger('room_number'); // 1-9, salah satu dari 9 "ruang zoom" virtual
            $table->enum('status', ['mendatang', 'selesai', 'dibatalkan'])->default('mendatang');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zoom_reservations');
    }
};
