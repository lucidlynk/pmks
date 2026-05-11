<?php

namespace App\Models;

use App\Enums\DtsenStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class DtsenRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference_number',
        'village_id',
        'user_id',
        'status',
        'purpose',
        'notes',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'status'       => DtsenStatus::class,
        'processed_at' => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function residents(): BelongsToMany
    {
        return $this->belongsToMany(Resident::class, 'dtsen_request_residents')
                    ->withTimestamps();
    }

    public function documents(): HasMany
    {
        return $this->hasMany(DtsenDocument::class);
    }

    public function currentDocument(): HasOne
    {
        return $this->hasOne(DtsenDocument::class)
                    ->where('is_current', true)
                    ->latestOfMany();
    }

    // ─── Business Logic ───────────────────────────────────────────

    public static function generateReferenceNumber(): string
    {
        $year  = now()->format('Y');
        $month = now()->format('m');

        $count = static::whereYear('created_at', $year)
                       ->whereMonth('created_at', $month)
                       ->withTrashed()
                       ->count() + 1;

        return sprintf('DTSEN/%s/%s/%04d', $year, $month, $count);
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    public function canBeEditedBy(User $user): bool
    {
        return $this->status->canEdit() && $this->isOwnedBy($user);
    }
}
