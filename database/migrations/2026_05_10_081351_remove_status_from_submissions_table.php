<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pmks_submissions', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('psks_submissions', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

    public function down(): void
    {
        Schema::table('pmks_submissions', function (Blueprint $table) {
            $table->string('status')->default('draft')->after('category_id');
        });

        Schema::table('psks_submissions', function (Blueprint $table) {
            $table->string('status')->default('draft')->after('category_id');
        });
    }
};
