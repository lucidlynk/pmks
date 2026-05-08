<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submission_batches', function (Blueprint $table) {
            $table->unique(['village_id', 'period_year'], 'unique_batch_per_village_per_year');
        });
    }

    public function down(): void
    {
        Schema::table('submission_batches', function (Blueprint $table) {
            $table->dropUnique('unique_batch_per_village_per_year');
        });
    }
};
