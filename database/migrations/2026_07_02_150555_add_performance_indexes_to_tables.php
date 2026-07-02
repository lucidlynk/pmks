<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submission_batches', function (Blueprint $table) {
            $table->index('status');
            $table->index('period_year');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('action');
        });

        Schema::table('residents', function (Blueprint $table) {
            $table->index(['village_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('submission_batches', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['period_year']);
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['action']);
        });

        Schema::table('residents', function (Blueprint $table) {
            $table->dropIndex(['village_id', 'is_active']);
        });
    }
};
