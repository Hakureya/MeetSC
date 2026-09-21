<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_forms', function (Blueprint $table) {
            // Keterangan formulir yang diisi oleh pembuat (bukan oleh peserta),
            // sejajar dengan judul dan batas waktu pengisian.
            $table->string('pic')->nullable()->after('judul');
            $table->string('tempat')->nullable()->after('pic');
            $table->string('rapat_pertemuan')->nullable()->after('tempat');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_forms', function (Blueprint $table) {
            $table->dropColumn(['pic', 'tempat', 'rapat_pertemuan']);
        });
    }
};