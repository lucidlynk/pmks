<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resident extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'village_id',
        'family_card_id',
        'nik',
        'name',
        'birth_place',
        'birth_date',
        'gender',
        'status_hubungan',
        'phone',
        'is_active',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_active'  => 'boolean',
    ];

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function familyCard(): BelongsTo
    {
        return $this->belongsTo(FamilyCard::class);
    }

    public function pmksSubmissions(): HasMany
    {
        return $this->hasMany(PmksSubmission::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}