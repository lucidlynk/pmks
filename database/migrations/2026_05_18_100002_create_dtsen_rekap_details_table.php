<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dtsen_rekap_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dtsen_rekap_id')
                  ->constrained('dtsen_rekaps')
                  ->cascadeOnDelete();
            $table->string('kecamatan');
            $table->string('kelurahan');
            $table->unsignedInteger('jumlah_keluarga')->default(0);
            $table->unsignedInteger('jumlah_individu')->default(0);
            $table->unsignedInteger('desil1_keluarga')->default(0);
            $table->unsignedInteger('desil1_individu')->default(0);
            $table->unsignedInteger('desil2_keluarga')->default(0);
            $table->unsignedInteger('desil2_individu')->default(0);
            $table->unsignedInteger('desil3_keluarga')->default(0);
            $table->unsignedInteger('desil3_individu')->default(0);
            $table->unsignedInteger('desil4_keluarga')->default(0);
            $table->unsignedInteger('desil4_individu')->default(0);
            $table->unsignedInteger('desil5_keluarga')->default(0);
            $table->unsignedInteger('desil5_individu')->default(0);
            $table->unsignedInteger('desil6_10_keluarga')->default(0);
            $table->unsignedInteger('desil6_10_individu')->default(0);
            $table->unsignedInteger('belum_peringkat_keluarga')->default(0);
            $table->unsignedInteger('belum_peringkat_individu')->default(0);
            $table->unsignedInteger('nonaktif_keluarga')->default(0);
            $table->unsignedInteger('nonaktif_individu')->default(0);
            $table->timestamps();

            $table->index(['dtsen_rekap_id', 'kecamatan'], 'detail_rekap_kec_idx');
            $table->index(['dtsen_rekap_id', 'kelurahan'], 'detail_rekap_kel_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtsen_rekap_details');
    }
};
