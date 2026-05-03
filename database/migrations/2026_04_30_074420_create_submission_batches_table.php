<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('village_id')
                  ->constrained('villages')
                  ->cascadeOnDelete();
            $table->foreignId('submitted_by')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->unsignedTinyInteger('period_month');
            $table->unsignedSmallInteger('period_year');
            $table->enum('status', ['open', 'finalized', 'approved', 'rejected'])
                  ->default('open');
            $table->string('letter_file_path')->nullable();
            $table->timestamp('letter_uploaded_at')->nullable();
            $table->foreignId('reviewed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_batches');
    }
};