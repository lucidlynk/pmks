<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('psks_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_batch_id')
                  ->constrained('submission_batches')
                  ->cascadeOnDelete();
            $table->string('original_filename')->nullable();
            $table->string('file_path');
            $table->enum('status', ['pending', 'processing', 'done', 'failed'])->default('pending');
            $table->string('job_batch_id')->nullable();
            $table->unsignedInteger('total_rows')->nullable();
            $table->unsignedInteger('success_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->json('error_summary')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('psks_imports');
    }
};
