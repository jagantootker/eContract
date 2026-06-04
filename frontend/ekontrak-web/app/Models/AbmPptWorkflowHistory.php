<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbmPptWorkflowHistory extends Model
{
    protected $table = 'abm_ppt_workflow_history';
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

    /**
     * Relationship: upload
     */
    public function upload(): BelongsTo
    {
        return $this->belongsTo(AbmPptUpload::class, 'upload_id');
    }

    /**
     * Get action label
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'UPLOADED' => 'Dimuat Naik',
            'EXTRACTED' => 'Data Diekstrak',
            'VERIFIED' => 'Disahkan',
            'APPROVED' => 'Diluluskan',
            'REJECTED' => 'Ditolak',
            'SUBMITTED' => 'Diserahkan',
            'COMPLETED' => 'Selesai',
            default => $this->action,
        };
    }
}
