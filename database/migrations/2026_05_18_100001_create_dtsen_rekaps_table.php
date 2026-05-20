<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dtsen_rekaps', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('bulan');
            $table->unsignedSmallInteger('tahun');
            $table->string('file_path');
            $table->string('original_filename');
            $table->string('keterangan')->nullable();
            $table->foreignId('uploaded_by')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['bulan', 'tahun'], 'dtsen_rekap_periode_unique');
            $table->index(['tahun', 'bulan']);
            $table->index('uploaded_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtsen_rekaps');
    }
};
