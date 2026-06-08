<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EotKontrak extends Model
{
    protected $table = 'eot_kontrak';

    protected $fillable = [
        'kontrak_id',
        'tarikh_mula_baru',
        'tarikh_tamat_baru',
        'sebab',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'tarikh_mula_baru'  => 'date',
            'tarikh_tamat_baru' => 'date',
        ];
    }

    public function kontrak(): BelongsTo
    {
        return $this->belongsTo(Kontrak::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
