<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $table = 'audit_log';

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'payload',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Static Helper ───────────────────────────────────────────────────────

    public static function record(
        string $action,
        string $modelType,
        ?int $modelId = null,
        ?array $payload = null,
        ?int $userId = null,
        ?string $ipAddress = null,
    ): self {
        return self::create([
            'user_id'    => $userId ?? auth()->id(),
            'action'     => $action,
            'model_type' => $modelType,
            'model_id'   => $modelId,
            'payload'    => $payload,
            'ip_address' => $ipAddress ?? request()->ip(),
        ]);
    }
}
