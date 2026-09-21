<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Divisi PIC: divisi/unit kerja penanggung jawab rapat. Dipisahkan dari
     * kolom "divisi" milik user, karena PIC rapat belum tentu orang yang memesan.
     */
    public function up(): void
    {
        Schema::table('room_reservations', function (Blueprint $table) {
            $table->string('divisi_pic')->nullable()->after('no_telp_pic');
        });
    }

    public function down(): void
    {
        Schema::table('room_reservations', function (Blueprint $table) {
            $table->dropColumn('divisi_pic');
        });
    }
};
