<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submission_batches', function (Blueprint $table) {
            // Ganti period_month ke period_year saja
            $table->dropColumn('period_month');

            // Tambah status baru
            $table->enum('status', [
                'draft',
                'submitted',
                'verified',
                'approved',
                'rejected',
                'revision_requested',
                'revised',
            ])->default('draft')->change();

            // Tambah kolom baru
            $table->foreignId('verified_by')
                  ->nullable()
                  ->after('reviewed_by')
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->text('verification_notes')->nullable()->after('verified_at');

            // Rename reviewed → approved untuk lebih jelas
            $table->renameColumn('reviewed_by', 'approved_by');
            $table->renameColumn('reviewed_at', 'approved_at');
            $table->renameColumn('rejection_reason', 'rejection_notes');

            // Draft surat
            $table->string('draft_letter_path')->nullable()->after('letter_file_path');
            $table->timestamp('draft_generated_at')->nullable()->after('draft_letter_path');
        });
    }

    public function down(): void
    {
        Schema::table('submission_batches', function (Blueprint $table) {
            $table->unsignedTinyInteger('period_month')->nullable();
            $table->dropColumn([
                'verified_by', 'verified_at', 'verification_notes',
                'draft_letter_path', 'draft_generated_at',
            ]);
            $table->renameColumn('approved_by', 'reviewed_by');
            $table->renameColumn('approved_at', 'reviewed_at');
            $table->renameColumn('rejection_notes', 'rejection_reason');
        });
    }
};