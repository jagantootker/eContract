<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatatanKontrak extends Model
{
    protected $table = 'catatan_kontrak';

    protected $fillable = [
        'kontrak_id',
        'user_id',
        'status',
        'tahap',
        'catatan',
    ];

    public function kontrak(): BelongsTo
    {
        return $this->belongsTo(Kontrak::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
