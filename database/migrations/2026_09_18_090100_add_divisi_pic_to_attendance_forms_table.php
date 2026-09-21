<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Divisi PIC: divisi/unit kerja penanggung jawab formulir kehadiran,
     * sejajar dengan kolom "pic" yang sudah ada (nama PIC-nya).
     */
    public function up(): void
    {
        Schema::table('attendance_forms', function (Blueprint $table) {
            $table->string('divisi_pic')->nullable()->after('pic');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_forms', function (Blueprint $table) {
            $table->dropColumn('divisi_pic');
        });
    }
};
