<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Institution extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'village_id',
        'name',
        'type',
        'registration_number',
        'address',
        'contact_person',
        'phone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function psksSubmissions(): HasMany
    {
        return $this->hasMany(PsksSubmission::class, 'subject_id')
                    ->where('subject_type', 'institution');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}