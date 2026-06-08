<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abm_v3_uploads', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->string('filename');
            $table->string('original_filename');
            $table->string('file_path');
            $table->string('template_type')->nullable();
            $table->string('file_type')->default('EXCEL');
            $table->bigInteger('uploaded_by')->nullable();
            $table->string('uploaded_by_name')->nullable();
            $table->string('status')->default('DRAFT');
            $table->json('workbook_data')->nullable();
            $table->json('summary_data')->nullable();
            $table->integer('total_rows')->default(0);
            $table->integer('total_sections')->default(0);
            $table->integer('total_sheets')->default(0);
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->integer('year')->nullable();
            $table->string('department')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['template_type']);
            $table->index(['uploaded_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abm_v3_uploads');
    }
};