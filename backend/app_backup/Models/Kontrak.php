<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kontrak extends Model
{
    use SoftDeletes;

    protected $table = 'kontrak';

    protected $fillable = [
        'no_kontrak',
        'tajuk_kontrak',
        'syarikat_id',
        'nilai_kontrak',
        'kaedah_perolehan',
        'kategori_perolehan',
        'pihak_berkuasa_melulus_nama',
        'pihak_berkuasa_melulus_tarikh',
        'diluluskan_tarikh',
        'ditandatangani_tarikh',
        'mula_tarikh',
        'tamat_tarikh',
        'tarikh_sst',
        'status_kontrak',
        'status_draf_kompan',
        'tarikh_draf_hantar_sistem',
        'catatan_kontrak',
        'jabatan_id',
        'bahagian_unit_id',
        'pegawai_bertanggungjawab_id',
        'pegawai_perhubungan_1_id',
        'pegawai_perhubungan_2_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'nilai_kontrak'                  => 'decimal:2',
            'status_draf_kompan'             => 'boolean',
            'pihak_berkuasa_melulus_tarikh'  => 'date',
            'diluluskan_tarikh'              => 'date',
            'ditandatangani_tarikh'          => 'date',
            'mula_tarikh'                    => 'date',
            'tamat_tarikh'                   => 'date',
            'tarikh_sst'                     => 'date',
            'tarikh_draf_hantar_sistem'      => 'date',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    public function syarikat(): BelongsTo
    {
        return $this->belongsTo(Syarikat::class);
    }

    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function bahagianUnit(): BelongsTo
    {
        return $this->belongsTo(BahagianUnit::class);
    }

    public function pegawaiBertanggungjawab(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pegawai_bertanggungjawab_id');
    }

    public function pegawaiPerhubungan1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pegawai_perhubungan_1_id');
    }

    public function pegawaiPerhubungan2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pegawai_perhubungan_2_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function catatan(): HasMany
    {
        return $this->hasMany(CatatanKontrak::class);
    }

    public function eot(): HasMany
    {
        return $this->hasMany(EotKontrak::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->tamat_tarikh
            && $this->tamat_tarikh->isFuture()
            && $this->tamat_tarikh->diffInDays(now()) <= $days;
    }

    public function isExpired(): bool
    {
        return $this->tamat_tarikh && $this->tamat_tarikh->isPast();
    }
}
