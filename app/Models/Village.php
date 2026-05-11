<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Village extends Model
{
    use HasFactory;

    protected $fillable = [
        'kecamatan_id',
        'name',
        'code',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function familyCards(): HasMany
    {
        return $this->hasMany(FamilyCard::class);
    }

    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class);
    }

    public function institutions(): HasMany
    {
        return $this->hasMany(Institution::class);
    }

    public function submissionBatches(): HasMany
    {
        return $this->hasMany(SubmissionBatch::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
