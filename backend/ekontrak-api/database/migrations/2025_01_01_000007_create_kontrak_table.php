<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kontrak', function (Blueprint $table) {
            $table->id();
            $table->string('no_kontrak', 50)->unique()->comment('Format: {BAHAGIAN}/{TYPE}/{YEAR} e.g. BTM/MG/2025');
            $table->text('tajuk_kontrak');
            $table->foreignId('syarikat_id')->constrained('syarikat')->restrictOnDelete();
            $table->decimal('nilai_kontrak', 15, 2);
            $table->enum('kaedah_perolehan', [
                'SEBUT HARGA',
                'TENDER',
                'RUNDINGAN TERUS',
                'PEMBELIAN TERUS',
            ])->nullable();
            $table->enum('kategori_perolehan', [
                'PERKHIDMATAN',
                'BEKALAN',
                'KERJA',
            ])->nullable();

            $table->string('pihak_berkuasa_melulus_nama', 255)->nullable();
            $table->date('pihak_berkuasa_melulus_tarikh')->nullable();
            $table->date('diluluskan_tarikh')->nullable();
            $table->date('ditandatangani_tarikh')->nullable();
            $table->date('mula_tarikh')->nullable();
            $table->date('tamat_tarikh')->nullable();
            $table->date('tarikh_sst')->nullable()->comment('SST/GST date');

            $table->enum('status_kontrak', [
                'DRAF',
                'DALAM_PELAKSANAAN',
                'KONTRAK_SELESAI',
                'EOT',
            ])->default('DRAF');

            $table->boolean('status_draf_kompan')->default(false)->comment('Telah Draf Kompan');
            $table->date('tarikh_draf_hantar_sistem')->nullable();
            $table->text('catatan_kontrak')->nullable();

            $table->foreignId('jabatan_id')->nullable()->constrained('jabatan')->nullOnDelete();
            $table->foreignId('bahagian_unit_id')->nullable()->constrained('bahagian_unit')->nullOnDelete();
            $table->foreignId('pegawai_bertanggungjawab_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('pegawai_perhubungan_1_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('pegawai_perhubungan_2_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('status_kontrak');
            $table->index('tamat_tarikh');
            $table->index('no_kontrak');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kontrak');
    }
};
