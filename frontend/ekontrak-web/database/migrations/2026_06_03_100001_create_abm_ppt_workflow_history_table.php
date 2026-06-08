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
        Schema::create('abm_ppt_workflow_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upload_id')->constrained('abm_ppt_uploads')->onDelete('cascade');
            $table->string('action')->comment('UPLOADED, EXTRACTED, VERIFIED, APPROVED, REJECTED, SUBMITTED, COMPLETED');
            $table->text('description')->nullable();
            $table->bigInteger('performed_by')->nullable()->comment('User ID who performed action');
            $table->string('performed_by_name')->nullable();
            $table->text('metadata')->nullable()->comment('JSON additional data');
            $table->timestamps();

            $table->index('upload_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abm_ppt_workflow_history');
    }
};
