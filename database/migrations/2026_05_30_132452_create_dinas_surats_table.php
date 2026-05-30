<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dinas_surats', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('nomor_surat')->nullable();
            $table->date('tanggal_surat');
            $table->enum('kategori', ['edaran', 'sk', 'pengumuman', 'lainnya'])->default('edaran');
            $table->text('deskripsi')->nullable();
            $table->string('file_path');
            $table->string('original_filename');
            $table->unsignedBigInteger('file_size')->default(0)->comment('bytes');
            $table->enum('target_scope', ['semua', 'kecamatan'])->default('semua');
            $table->json('kecamatan_ids')->nullable()->comment('Null jika target_scope=semua');
            $table->boolean('is_active')->default(true);
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('kategori');
            $table->index('tanggal_surat');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dinas_surats');
    }
};
