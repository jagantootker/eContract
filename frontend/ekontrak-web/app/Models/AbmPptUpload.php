<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AbmPptUpload extends Model
{
    protected $table = 'abm_ppt_uploads';
    protected $fillable = [
        'reference_no',
        'filename',
        'original_filename',
        'file_path',
        'template_type',
        'file_type',
        'uploaded_by',
        'uploaded_by_name',
        'status',
        'extraction_data',
        'total_records',
        'rejection_reason',
        'notes',
        'year',
        'department',
        'officer_name',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'extraction_data' => 'array',
    ];

    /**
     * Relationship: workflow history
     */
    public function workflowHistory(): HasMany
    {
        return $this->hasMany(AbmPptWorkflowHistory::class, 'upload_id');
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'DRAFT' => 'Draf',
            'SEDANG_DISEMAK' => 'Sedang Disemak',
            'DILULUSKAN' => 'Diluluskan',
            'DITOLAK' => 'Ditolak',
            'SELESAI' => 'Selesai',
            default => $this->status,
        };
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'DRAFT' => 'bg-blue-100 text-blue-800',
            'SEDANG_DISEMAK' => 'bg-yellow-100 text-yellow-800',
            'DILULUSKAN' => 'bg-green-100 text-green-800',
            'DITOLAK' => 'bg-red-100 text-red-800',
            'SELESAI' => 'bg-gray-100 text-gray-800',
            default => 'bg-slate-100 text-slate-800',
        };
    }

    /**
     * Template type label
     */
    public function getTemplateTypeLabelAttribute(): string
    {
        return match($this->template_type) {
            'ABM1' => 'ABM 1',
            'ABM2' => 'ABM 2',
            'ABM3' => 'ABM 3',
            'ABM4' => 'ABM 4',
            'ABM5' => 'ABM 5',
            'ABM6' => 'ABM 6',
            'ABM7' => 'ABM 7',
            'ABM7A' => 'ABM 7A',
            'ABM7B' => 'ABM 7B',
            'ABM8' => 'ABM 8',
            'PPT_BARU' => 'PPT Baru',
            'PPT_KEMAS_KINI' => 'PPT Kemas Kini',
            default => $this->template_type,
        };
    }
}
