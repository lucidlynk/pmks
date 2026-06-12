<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pmks_imports', function (Blueprint $table) {
            $table->dropForeign(['submission_batch_id']);
            $table->unsignedBigInteger('submission_batch_id')->nullable()->change();
            $table->foreign('submission_batch_id')->references('id')->on('submission_batches')->cascadeOnDelete();
            $table->enum('import_mode', ['per_desa', 'kabupaten'])->default('per_desa')->after('submission_batch_id');
            $table->unsignedSmallInteger('period_year')->nullable()->after('import_mode');
        });

        Schema::table('psks_imports', function (Blueprint $table) {
            $table->dropForeign(['submission_batch_id']);
            $table->unsignedBigInteger('submission_batch_id')->nullable()->change();
            $table->foreign('submission_batch_id')->references('id')->on('submission_batches')->cascadeOnDelete();
            $table->enum('import_mode', ['per_desa', 'kabupaten'])->default('per_desa')->after('submission_batch_id');
            $table->unsignedSmallInteger('period_year')->nullable()->after('import_mode');
        });
    }

    public function down(): void
    {
        Schema::table('pmks_imports', function (Blueprint $table) {
            $table->dropForeign(['submission_batch_id']);
            $table->unsignedBigInteger('submission_batch_id')->nullable(false)->change();
            $table->foreign('submission_batch_id')->references('id')->on('submission_batches')->cascadeOnDelete();
            $table->dropColumn(['import_mode', 'period_year']);
        });

        Schema::table('psks_imports', function (Blueprint $table) {
            $table->dropForeign(['submission_batch_id']);
            $table->unsignedBigInteger('submission_batch_id')->nullable(false)->change();
            $table->foreign('submission_batch_id')->references('id')->on('submission_batches')->cascadeOnDelete();
            $table->dropColumn(['import_mode', 'period_year']);
        });
    }
};
