<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('ic_number', 12)->unique()->comment('Malaysian IC number, no dashes');
            $table->string('name', 255);
            $table->string('email', 255)->unique();
            $table->string('jabatan_bahagian', 255)->nullable()->comment('Department/Division');
            $table->string('bahagian_unit', 255)->nullable()->comment('Unit');
            $table->string('telefon_pejabat', 20)->nullable();
            $table->string('telefon_bimbit', 20)->nullable();
            $table->string('password', 255)->comment('bcrypt hashed');
            $table->boolean('is_active')->default(true);
            $table->string('mfa_secret', 255)->nullable()->comment('TOTP secret');
            $table->enum('source', ['BTM', 'JBPM', 'AGENSI'])->default('BTM');
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('ic_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
