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
        Schema::create('abm_ppt_uploads', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique()->comment('Auto-generated reference number');
            $table->string('filename');
            $table->string('original_filename');
            $table->string('file_path');
            $table->enum('template_type', ['ABM1', 'ABM2', 'ABM3', 'ABM4', 'ABM5', 'ABM6', 'ABM7', 'ABM7A', 'ABM7B', 'ABM8', 'PPT_BARU', 'PPT_KEMAS_KINI']);
            $table->enum('file_type', ['EXCEL', 'PDF'])->comment('EXCEL or PDF');
            $table->bigInteger('uploaded_by')->comment('User ID who uploaded');
            $table->string('uploaded_by_name')->nullable();
            $table->enum('status', ['DRAFT', 'SEDANG_DISEMAK', 'DILULUSKAN', 'DITOLAK', 'SELESAI'])->default('DRAFT');
            $table->text('extraction_data')->nullable()->comment('JSON data extracted from file');
            $table->integer('total_records')->default(0)->comment('Number of records extracted');
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->integer('year')->nullable()->comment('Tahun (Year)');
            $table->string('department')->nullable()->comment('Bahagian/Unit');
            $table->string('officer_name')->nullable()->comment('Pegawai name');
            $table->timestamps();

            // Indexes
            $table->index('template_type');
            $table->index('status');
            $table->index('uploaded_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abm_ppt_uploads');
    }
};
