<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_reservations', function (Blueprint $table) {
            $table->string('nama_pic')->nullable()->after('keperluan');
            $table->string('no_telp_pic')->nullable()->after('nama_pic');

            // Dipakai untuk reservasi ruang gabungan: reservasi utama (induk) pada ruang
            // gabungan otomatis membuat reservasi turunan pada tiap ruang komponennya di
            // jam yang sama, agar ruang komponen ikut terkunci / tidak bisa dipesan ganda.
            $table->foreignId('parent_reservation_id')
                ->nullable()
                ->after('room_id')
                ->constrained('room_reservations')
                ->nullOnDelete();

            $table->boolean('auto_generated')->default(false)->after('parent_reservation_id');
        });
    }

    public function down(): void
    {
        Schema::table('room_reservations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_reservation_id');
            $table->dropColumn(['nama_pic', 'no_telp_pic', 'auto_generated']);
        });
    }
};