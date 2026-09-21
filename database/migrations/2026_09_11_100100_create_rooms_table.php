<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // Ruang Solution, Ruang Chain, dst
            $table->unsignedTinyInteger('floor'); // 1, 2, atau 3
            $table->unsignedInteger('capacity_min'); // kapasitas minimal (mis. 6)
            $table->unsignedInteger('capacity_max'); // kapasitas maksimal (mis. 12; sama dengan min kalau tidak berbentuk rentang)
            $table->boolean('is_combined')->default(false); // true untuk ruang gabungan, mis. "Adaptif + Kompeten"
            $table->json('combined_room_ids')->nullable();  // id ruang-ruang dasar penyusun, hanya diisi jika is_combined = true
            $table->string('image')->nullable();  // path gambar ruangan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
