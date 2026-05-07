<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pmks_submissions', function (Blueprint $table) {
            // Gunakan text agar aman di MariaDB HG680P
            $table->text('disability_types')->nullable()->after('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('pmks_submissions', function (Blueprint $table) {
            $table->dropColumn('disability_types');
        });
    }
};
