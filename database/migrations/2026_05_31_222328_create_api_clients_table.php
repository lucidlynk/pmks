<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_clients', function (Blueprint $table) {
            $table->id();
            $table->string('nama_instansi');
            $table->string('keterangan')->nullable();
            $table->unsignedBigInteger('token_id')->nullable()->comment('FK ke personal_access_tokens');
            $table->string('token_preview', 10)->nullable()->comment('8 karakter pertama token untuk identifikasi');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
            $table->index('token_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_clients');
    }
};
