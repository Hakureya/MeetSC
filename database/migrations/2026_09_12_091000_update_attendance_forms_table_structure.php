<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Struktur attendance_forms sebelumnya (title/slug) tidak sesuai dengan yang
     * dipakai AttendanceFormController & view builder-nya (uuid/judul/fields json).
     * Migration ini menyesuaikan strukturnya supaya fitur Form Kehadiran bisa jalan.
     */
    public function up(): void
    {
        Schema::table('attendance_forms', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('user_id');
            $table->string('judul')->nullable()->after('uuid');
            $table->json('fields')->nullable()->after('judul');
        });

        // Migrasikan data lama (kalau ada) supaya tidak hilang begitu saja.
        DB::table('attendance_forms')->orderBy('id')->each(function ($row) {
            DB::table('attendance_forms')->where('id', $row->id)->update([
                'uuid' => $row->uuid ?? (string) Str::uuid(),
                'judul' => $row->judul ?? $row->title ?? 'Form Kehadiran',
                'fields' => $row->fields ?? json_encode([
                    ['nama' => 'Nama Lengkap', 'tipe' => 'text', 'required' => true],
                ]),
            ]);
        });

        Schema::table('attendance_forms', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->unique()->change();
            $table->string('judul')->nullable(false)->change();
            $table->json('fields')->nullable(false)->change();
            $table->dropUnique(['slug']);
            $table->dropColumn(['title', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::table('attendance_forms', function (Blueprint $table) {
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->dropColumn(['uuid', 'judul', 'fields']);
        });
    }
};
