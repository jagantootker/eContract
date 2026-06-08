<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AbmV3Upload extends Model
{
    protected $table = 'abm_v3_uploads';

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
        'workbook_data',
        'summary_data',
        'total_rows',
        'total_sections',
        'total_sheets',
        'total_amount',
        'year',
        'department',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'workbook_data' => 'array',
        'summary_data' => 'array',
    ];

    public function workflowHistory(): HasMany
    {
        return $this->hasMany(AbmV3WorkflowHistory::class, 'upload_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'DRAFT' => 'Draf',
            'DIEXTRACT' => 'Diekstrak',
            'SEMAK' => 'Dalam Semakan',
            'DILULUSKAN' => 'Diluluskan',
            'DITOLAK' => 'Ditolak',
            'SELESAI' => 'Selesai',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'DRAFT' => 'bg-slate-100 text-slate-700',
            'DIEXTRACT' => 'bg-sky-100 text-sky-700',
            'SEMAK' => 'bg-amber-100 text-amber-700',
            'DILULUSKAN' => 'bg-emerald-100 text-emerald-700',
            'DITOLAK' => 'bg-rose-100 text-rose-700',
            'SELESAI' => 'bg-indigo-100 text-indigo-700',
            default => 'bg-slate-100 text-slate-700',
        };
    }

    public function getTemplateTypeLabelAttribute(): string
    {
        return match ($this->template_type) {
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
            default => $this->template_type ?? 'ABM',
        };
    }
}