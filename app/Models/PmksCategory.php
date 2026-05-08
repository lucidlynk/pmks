<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PmksCategory extends Model
{
    protected $fillable = [
        'code', 'name', 'description', 'is_active',
        'min_age', 'max_age', 'gender_restriction',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'min_age'   => 'integer',
        'max_age'   => 'integer',
    ];

    public function pmksSubmissions(): HasMany
    {
        return $this->hasMany(PmksSubmission::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function hasAgeRestriction(): bool
    {
        return $this->min_age !== null || $this->max_age !== null;
    }

    public function hasGenderRestriction(): bool
    {
        return $this->gender_restriction !== null;
    }

    public function ageLabel(): string
    {
        if ($this->min_age === null && $this->max_age === null) {
            return 'Semua usia';
        }
        if ($this->max_age === null) {
            return "{$this->min_age} tahun ke atas";
        }
        if ($this->min_age === null) {
            return "Sampai {$this->max_age} tahun";
        }
        return "{$this->min_age}-{$this->max_age} tahun";
    }

    public function genderLabel(): string
    {
        return match($this->gender_restriction) {
            'L'     => 'Laki-laki',
            'P'     => 'Perempuan',
            default => 'Semua gender',
        };
    }
}
