<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bahagian_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jabatan_id')->constrained('jabatan')->cascadeOnDelete();
            $table->string('kod', 20);
            $table->string('nama', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bahagian_unit');
    }
};
