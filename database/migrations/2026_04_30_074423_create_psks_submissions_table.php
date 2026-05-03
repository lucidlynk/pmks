<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('psks_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')
                  ->constrained('submission_batches')
                  ->cascadeOnDelete();
            $table->foreignId('village_id')
                  ->constrained('villages')
                  ->cascadeOnDelete();
            $table->foreignId('category_id')
                  ->constrained('psks_categories')
                  ->cascadeOnDelete();
            $table->enum('subject_type', ['person', 'institution']);
            $table->unsignedBigInteger('subject_id');
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])
                  ->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('input_by')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('psks_submissions');
    }
};