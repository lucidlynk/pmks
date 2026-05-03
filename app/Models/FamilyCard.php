<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FamilyCard extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'village_id',
        'no_kk',
        'kepala_keluarga',
        'address',
        'rt',
        'rw',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}