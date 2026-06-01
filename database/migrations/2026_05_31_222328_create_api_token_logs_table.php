<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_token_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_client_id')->constrained('api_clients')->cascadeOnDelete();
            $table->string('endpoint', 100);
            $table->string('method', 10)->default('GET');
            $table->json('parameters')->nullable()->comment('Query parameters yang dikirim');
            $table->unsignedSmallInteger('response_code')->default(200);
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('accessed_at');

            $table->index('api_client_id');
            $table->index('accessed_at');
            $table->index('endpoint');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_token_logs');
    }
};
