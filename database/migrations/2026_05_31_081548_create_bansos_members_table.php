<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bansos_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')->constrained('bansos_imports')->cascadeOnDelete();
            $table->string('nama_penerima', 100);
            $table->string('nik', 16)->comment('NIK di-mask 4 digit terakhir oleh Kemensos');
            $table->string('nokk', 16)->nullable();
            $table->string('penyaluran_oleh', 50)->nullable();
            $table->enum('jenis_bansos', ['pkh', 'sembako']);
            $table->string('kec_name', 100)->nullable();
            $table->string('kel_name', 100)->nullable();
            $table->string('status_bansos', 50);
            $table->string('kode_batch', 50)->nullable();
            $table->unsignedTinyInteger('triwulan');
            $table->unsignedSmallInteger('tahun');
            $table->timestamps();

            $table->index(['jenis_bansos', 'triwulan', 'tahun']);
            $table->index(['kec_name', 'kel_name']);
            $table->index('nik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bansos_members');
    }
};
