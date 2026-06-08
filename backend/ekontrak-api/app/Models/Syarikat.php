<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Syarikat extends Model
{
    use SoftDeletes;

    protected $table = 'syarikat';

    protected $fillable = [
        'nama_syarikat',
        'alamat',
        'negeri',
        'pegawai_hubungi_1_nama',
        'pegawai_hubungi_1_email',
        'pegawai_hubungi_1_tel_pejabat',
        'pegawai_hubungi_1_tel_hp',
        'pegawai_hubungi_2_nama',
        'pegawai_hubungi_2_email',
        'pegawai_hubungi_2_tel_pejabat',
        'pegawai_hubungi_2_tel_hp',
        'pegawai_hubungi_3_nama',
        'pegawai_hubungi_3_email',
        'pegawai_hubungi_3_tel_pejabat',
        'pegawai_hubungi_3_tel_hp',
        'created_by',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function kontrak(): HasMany
    {
        return $this->hasMany(Kontrak::class);
    }
}
