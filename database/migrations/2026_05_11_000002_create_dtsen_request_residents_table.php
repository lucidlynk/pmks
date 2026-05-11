<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dtsen_request_residents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dtsen_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resident_id')->constrained()->restrictOnDelete()
                  ->comment('Tidak boleh hapus resident jika masih ada permohonan aktif');
            $table->timestamps();

            $table->unique(['dtsen_request_id', 'resident_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtsen_request_residents');
    }
};
