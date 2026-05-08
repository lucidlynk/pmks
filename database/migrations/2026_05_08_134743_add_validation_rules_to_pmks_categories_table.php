<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pmks_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('pmks_categories', 'min_age')) {
                $table->unsignedSmallInteger('min_age')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('pmks_categories', 'max_age')) {
                $table->unsignedSmallInteger('max_age')->nullable()->after('min_age')
                      ->comment('null = tidak ada batas atas usia');
            }
            if (!Schema::hasColumn('pmks_categories', 'gender_restriction')) {
                $table->char('gender_restriction', 1)->nullable()->after('max_age')
                      ->comment('L = Laki-laki, P = Perempuan, null = semua gender');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pmks_categories', function (Blueprint $table) {
            $cols = array_filter(
                ['min_age', 'max_age', 'gender_restriction'],
                fn ($col) => Schema::hasColumn('pmks_categories', $col)
            );
            if ($cols) $table->dropColumn(array_values($cols));
        });
    }
};
