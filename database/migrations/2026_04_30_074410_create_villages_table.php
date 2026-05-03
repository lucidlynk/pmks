<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('villages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kecamatan_id')
                  ->constrained('kecamatans')
                  ->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->enum('type', ['desa', 'kelurahan']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('villages');
    }
};