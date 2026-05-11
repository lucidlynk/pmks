<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dtsen_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 30)->unique()->comment('Format: DTSEN/YYYY/MM/NNNN');
            $table->foreignId('village_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete()->comment('Operator desa yang mengajukan');
            $table->string('status', 20)->default('draft');
            $table->text('purpose')->comment('Keperluan permohonan surat DTSEN');
            $table->text('notes')->nullable()->comment('Catatan dari Staf Dinsos');
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete()->comment('Staf Dinsos yang memproses');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['village_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtsen_requests');
    }
};
