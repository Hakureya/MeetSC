<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nip')->nullable()->after('email');
            $table->string('divisi')->nullable()->after('nip');
            $table->string('jabatan')->nullable()->after('divisi');
            $table->enum('role', ['admin', 'user'])->default('user')->after('jabatan');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->after('role');
            $table->timestamp('last_login_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nip', 'divisi', 'jabatan', 'role', 'status', 'last_login_at']);
        });
    }
};
