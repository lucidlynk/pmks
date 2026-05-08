<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pmks_submissions', function (Blueprint $table) {
            // Ubah enum lama ke string agar sinkron dengan BatchStatus
            // yang dikontrol oleh batch induknya
            $table->string('status')->default('draft')->change();
        });
    }

    public function down(): void
    {
        Schema::table('pmks_submissions', function (Blueprint $table) {
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])
                  ->default('draft')->change();
        });
    }
};