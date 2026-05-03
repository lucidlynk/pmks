<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PsksCategory extends Model
{
    protected $fillable = [
        'code',
        'name',
        'subject_type',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function psksSubmissions(): HasMany
    {
        return $this->hasMany(PsksSubmission::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}