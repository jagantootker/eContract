<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abm_v3_workflow_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upload_id')->constrained('abm_v3_uploads')->cascadeOnDelete();
            $table->string('action');
            $table->text('description')->nullable();
            $table->bigInteger('performed_by')->nullable();
            $table->string('performed_by_name')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['upload_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abm_v3_workflow_history');
    }
};