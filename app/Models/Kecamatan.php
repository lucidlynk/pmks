<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kecamatan extends Model
{
    protected $fillable = [
        'name',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function villages(): HasMany
    {
        return $this->hasMany(Village::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}