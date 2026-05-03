<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Satu resident boleh > 1 kategori, tapi tidak boleh kategori yang sama 2x dalam 1 batch
        Schema::table('pmks_submissions', function (Blueprint $table) {
            $table->unique(['batch_id', 'resident_id', 'category_id'], 'unique_pmks_per_batch');
        });

        // Satu subject (person/institution) tidak boleh kategori yang sama 2x dalam 1 batch
        Schema::table('psks_submissions', function (Blueprint $table) {
            $table->unique(
                ['batch_id', 'subject_type', 'subject_id', 'category_id'],
                'unique_psks_per_batch'
            );
        });
    }

    public function down(): void
    {
        Schema::table('pmks_submissions', function (Blueprint $table) {
            $table->dropUnique('unique_pmks_per_batch');
        });
        Schema::table('psks_submissions', function (Blueprint $table) {
            $table->dropUnique('unique_psks_per_batch');
        });
    }
};