<?php

namespace App\Models;

use App\Enums\DtsenStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class DtsenRequest extends Model
{
    use SoftDeletes;

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

    protected function casts(): array
    {
        return [
            'status'       => DtsenStatus::class,
            'processed_at' => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

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

    public function currentDocument(): HasMany
    {
        return $this->hasMany(DtsenDocument::class)->where('is_current', true);
    }

    // -------------------------------------------------------------------------
    // Business logic — status transitions
    // -------------------------------------------------------------------------

    public function submit(): void
    {
        abort_unless($this->status->canSubmit(), 422, 'Permohonan tidak dapat diajukan.');

        $this->update(['status' => DtsenStatus::SUBMITTED]);
    }

    public function process(User $staf): void
    {
        abort_unless($this->status->canProcess(), 422, 'Permohonan tidak dalam status yang bisa diproses.');

        $this->update([
            'status'       => DtsenStatus::ON_PROCESS,
            'processed_by' => $staf->id,
            'processed_at' => now(),
        ]);
    }

    public function cancel(): void
    {
        abort_unless($this->status->canCancel(), 422, 'Permohonan tidak dapat dibatalkan.');

        $this->update(['status' => DtsenStatus::CANCELLED]);
    }

    public function markReady(): void
    {
        abort_unless($this->status === DtsenStatus::ON_PROCESS, 422, 'Permohonan belum dalam proses.');

        $this->update(['status' => DtsenStatus::READY]);
    }

    // -------------------------------------------------------------------------
    // Reference number generator
    // -------------------------------------------------------------------------

    public static function generateReferenceNumber(): string
    {
        return DB::transaction(function () {
            $year  = now()->format('Y');
            $month = now()->format('m');
            $prefix = "DTSEN/{$year}/{$month}/";

            $last = self::withTrashed()
                ->where('reference_number', 'like', $prefix . '%')
                ->lockForUpdate()
                ->count();

            $sequence = str_pad($last + 1, 4, '0', STR_PAD_LEFT);

            return $prefix . $sequence;
        });
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeForVillage($query, int $villageId)
    {
        return $query->where('village_id', $villageId);
    }

    public function scopeByStatus($query, DtsenStatus $status)
    {
        return $query->where('status', $status->value);
    }
}
