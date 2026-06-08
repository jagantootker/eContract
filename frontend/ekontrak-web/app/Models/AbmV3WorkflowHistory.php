<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbmV3WorkflowHistory extends Model
{
    protected $table = 'abm_v3_workflow_history';

    protected $fillable = [
        'upload_id',
        'action',
        'description',
        'performed_by',
        'performed_by_name',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function upload(): BelongsTo
    {
        return $this->belongsTo(AbmV3Upload::class, 'upload_id');
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'UPLOADED' => 'Dimuat Naik',
            'EXTRACTED' => 'Diekstrak',
            'REVIEWED' => 'Disemak',
            'APPROVED' => 'Diluluskan',
            'REJECTED' => 'Ditolak',
            'COMPLETED' => 'Selesai',
            default => $this->action,
        };
    }
}