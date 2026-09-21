<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_form_id')->constrained()->cascadeOnDelete();
            $table->json('answers'); // { "Nama Lengkap": "Budi Santoso", "NIP / Unit": "..." }
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_responses');
    }
};
