<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
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

            $table->index('ic_number');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
