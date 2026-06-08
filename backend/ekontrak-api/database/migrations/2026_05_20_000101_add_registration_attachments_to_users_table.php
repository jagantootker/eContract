<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('no_rujukan_permohonan', 30)->nullable()->unique()->after('permohonan_status');
            $table->string('lampiran_borang_permohonan', 255)->nullable()->after('no_rujukan_permohonan');
            $table->string('lampiran_kp_tentera', 255)->nullable()->after('lampiran_borang_permohonan');
            $table->string('lampiran_pas_pekerja', 255)->nullable()->after('lampiran_kp_tentera');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'no_rujukan_permohonan',
                'lampiran_borang_permohonan',
                'lampiran_kp_tentera',
                'lampiran_pas_pekerja',
            ]);
        });
    }
};
