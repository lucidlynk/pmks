<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kis_pbi_apbd_imports', function (Blueprint $table) {
            $table->id();
            $table->string('original_filename');
            $table->string('file_path');
            $table->unsignedTinyInteger('periode_bulan')->comment('1-12');
            $table->unsignedSmallInteger('periode_tahun');
            $table->enum('status', ['pending', 'processing', 'done', 'failed'])->default('pending');
            $table->string('batch_id', 36)->nullable()->comment('Laravel job_batches.id');
            $table->unsignedInteger('total_rows')->nullable();
            $table->unsignedInteger('processed_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->json('error_summary')->nullable()->comment('Baris yang gagal beserta alasannya');
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['periode_bulan', 'periode_tahun']);
            $table->index('status');
            $table->index('batch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kis_pbi_apbd_imports');
    }
};
