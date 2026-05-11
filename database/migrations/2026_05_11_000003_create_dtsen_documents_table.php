<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dtsen_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dtsen_request_id')->constrained()->cascadeOnDelete();
            $table->string('file_path')->comment('Path relatif di private storage');
            $table->string('original_filename')->comment('Nama file asli saat upload');
            $table->unsignedBigInteger('file_size')->comment('Ukuran file dalam bytes');
            $table->boolean('is_current')->default(true)->comment('Hanya satu yang aktif per request');
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['dtsen_request_id', 'is_current']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtsen_documents');
    }
};
