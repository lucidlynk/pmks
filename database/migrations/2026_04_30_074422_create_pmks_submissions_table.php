<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pmks_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')
                  ->constrained('submission_batches')
                  ->cascadeOnDelete();
            $table->foreignId('village_id')
                  ->constrained('villages')
                  ->cascadeOnDelete();
            $table->foreignId('resident_id')
                  ->constrained('residents')
                  ->cascadeOnDelete();
            $table->foreignId('category_id')
                  ->constrained('pmks_categories')
                  ->cascadeOnDelete();
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
        Schema::dropIfExists('pmks_submissions');
    }
};