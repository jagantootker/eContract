<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('no_tentera', 20)->nullable()->after('ic_number');
            $table->enum('jenis_permohonan', [
                'pendaftaran_online',
                'pengaktifan_semula_id',
                'penukaran_peranan',
            ])->nullable()->after('source');
            $table->boolean('kategori_permohonan_agensi')->default(false)->after('jenis_permohonan');
            $table->boolean('kategori_permohonan_pengguna')->default(true)->after('kategori_permohonan_agensi');
            $table->string('capaian_peranan', 255)->nullable()->after('kategori_permohonan_pengguna');
            $table->string('akses_scope', 100)->nullable()->after('capaian_peranan');
            $table->enum('permohonan_status', ['pending', 'diluluskan', 'ditolak'])->default('pending')->after('akses_scope');

            $table->index('permohonan_status');
            $table->index('jenis_permohonan');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['permohonan_status']);
            $table->dropIndex(['jenis_permohonan']);
            $table->dropColumn([
                'no_tentera',
                'jenis_permohonan',
                'kategori_permohonan_agensi',
                'kategori_permohonan_pengguna',
                'capaian_peranan',
                'akses_scope',
                'permohonan_status',
            ]);
        });
    }
};
