<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kis_pbi_apbd_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')->constrained('kis_pbi_apbd_imports')->cascadeOnDelete();
            $table->string('psnoka', 20);
            $table->string('nik', 16);
            $table->string('nama', 100);
            $table->string('segmen', 30);
            $table->unsignedTinyInteger('periode_bulan');
            $table->unsignedSmallInteger('periode_tahun');
            $table->timestamps();

            $table->unique(['nik', 'periode_bulan', 'periode_tahun'], 'kis_member_nik_periode_unique');
            $table->index('nik');
            $table->index(['periode_bulan', 'periode_tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kis_pbi_apbd_members');
    }
};
