<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('village_id')
                  ->nullable()
                  ->after('email')
                  ->constrained('villages')
                  ->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('village_id');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->softDeletes()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('village_id');
            $table->dropColumn(['is_active', 'last_login_at']);
            $table->dropSoftDeletes();
        });
    }
};