<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jabatan extends Model
{
    protected $table = 'jabatan';
    protected $fillable = ['kod', 'nama', 'is_visible_in_registration'];

    protected $casts = [
        'is_visible_in_registration' => 'boolean',
    ];

    public function bahagianUnit(): HasMany
    {
        return $this->hasMany(BahagianUnit::class);
    }

    public function kontrak(): HasMany
    {
        return $this->hasMany(Kontrak::class);
    }
}
