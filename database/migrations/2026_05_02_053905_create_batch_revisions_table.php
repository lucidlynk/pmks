<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')
                  ->constrained('submission_batches')
                  ->cascadeOnDelete();
            $table->foreignId('requested_by')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->foreignId('approved_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])
                  ->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_revisions');
    }
};