<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kis_rekaps', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('periode_bulan')->comment('1-12');
            $table->unsignedSmallInteger('periode_tahun');
            $table->unsignedInteger('pbi_apbd')->default(0);
            $table->unsignedInteger('pbi_apbn')->default(0);
            $table->unsignedInteger('ppu')->default(0);
            $table->unsignedInteger('pbpu')->default(0);
            $table->unsignedInteger('bp')->default(0);
            $table->unsignedInteger('total')->default(0)->comment('Auto-computed: sum semua segmen');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['periode_bulan', 'periode_tahun'], 'kis_rekap_periode_unique');
            $table->index('periode_tahun');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kis_rekaps');
    }
};
