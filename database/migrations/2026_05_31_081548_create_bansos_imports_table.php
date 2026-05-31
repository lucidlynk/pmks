<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bansos_imports', function (Blueprint $table) {
            $table->id();
            $table->enum('jenis_bansos', ['pkh', 'sembako']);
            $table->enum('status_bansos', ['sudah_si', 'sudah_salur', 'sudah_transaksi']);
            $table->unsignedTinyInteger('triwulan')->comment('1-4');
            $table->unsignedSmallInteger('tahun');
            $table->string('original_filename');
            $table->string('file_path');
            $table->enum('status', ['pending', 'processing', 'done', 'failed'])->default('pending');
            $table->string('batch_id', 36)->nullable()->comment('Laravel job_batches.id');
            $table->unsignedInteger('total_rows')->nullable();
            $table->unsignedInteger('processed_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->json('error_summary')->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['jenis_bansos', 'status_bansos', 'triwulan', 'tahun']);
            $table->index('status');
            $table->index('batch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bansos_imports');
    }
};
