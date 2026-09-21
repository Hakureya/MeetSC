<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('zoom_reservations', function (Blueprint $table) {
            $table->string('nama_pic')->nullable()->after('nama_agenda');
            $table->string('no_telp_pic')->nullable()->after('nama_pic');
        });
    }

    public function down(): void
    {
        Schema::table('zoom_reservations', function (Blueprint $table) {
            $table->dropColumn(['nama_pic', 'no_telp_pic']);
        });
    }
};