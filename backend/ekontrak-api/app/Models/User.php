<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, SoftDeletes;

    protected $fillable = [
        'ic_number',
        'no_tentera',
        'name',
        'email',
        'jabatan_bahagian',
        'bahagian_unit',
        'telefon_pejabat',
        'telefon_bimbit',
        'password',
        'is_active',
        'mfa_secret',
        'source',
        'jenis_permohonan',
        'kategori_permohonan_agensi',
        'kategori_permohonan_pengguna',
        'capaian_peranan',
        'akses_scope',
        'permohonan_status',
        'no_rujukan_permohonan',
        'lampiran_borang_permohonan',
        'lampiran_kp_tentera',
        'lampiran_pas_pekerja',
        'last_login_at',
        'force_password_change',
    ];

    protected $hidden = [
        'password',
        'mfa_secret',
    ];

    protected function casts(): array
    {
        return [
            'is_active'     => 'boolean',
            'kategori_permohonan_agensi' => 'boolean',
            'kategori_permohonan_pengguna' => 'boolean',
            'last_login_at' => 'datetime',
            'force_password_change' => 'boolean',
            'password'      => 'hashed',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function kontrak(): HasMany
    {
        return $this->hasMany(Kontrak::class, 'created_by');
    }

    public function catatanKontrak(): HasMany
    {
        return $this->hasMany(CatatanKontrak::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }
}
