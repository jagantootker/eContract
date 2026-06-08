<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('syarikat', function (Blueprint $table) {
            $table->id();
            $table->string('nama_syarikat', 255);
            $table->text('alamat');
            $table->string('negeri', 100);

            // Pegawai Hubungi 1
            $table->string('pegawai_hubungi_1_nama', 255)->nullable();
            $table->string('pegawai_hubungi_1_email', 255)->nullable();
            $table->string('pegawai_hubungi_1_tel_pejabat', 20)->nullable();
            $table->string('pegawai_hubungi_1_tel_hp', 20)->nullable();

            // Pegawai Hubungi 2
            $table->string('pegawai_hubungi_2_nama', 255)->nullable();
            $table->string('pegawai_hubungi_2_email', 255)->nullable();
            $table->string('pegawai_hubungi_2_tel_pejabat', 20)->nullable();
            $table->string('pegawai_hubungi_2_tel_hp', 20)->nullable();

            // Pegawai Hubungi 3
            $table->string('pegawai_hubungi_3_nama', 255)->nullable();
            $table->string('pegawai_hubungi_3_email', 255)->nullable();
            $table->string('pegawai_hubungi_3_tel_pejabat', 20)->nullable();
            $table->string('pegawai_hubungi_3_tel_hp', 20)->nullable();

            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('syarikat');
    }
};
