<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DtsenDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'dtsen_request_id',
        'file_path',
        'original_filename',
        'file_size',
        'is_current',
        'uploaded_by',
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'file_size'  => 'integer',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function dtsenRequest(): BelongsTo
    {
        return $this->belongsTo(DtsenRequest::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // ─── Business Logic ───────────────────────────────────────────

    public function getDownloadUrl(): string
    {
        return route('dtsen.document.download', $this);
    }

    public function getFileSizeForHumans(): string
    {
        $bytes = $this->file_size;

        return match(true) {
            $bytes >= 1_048_576 => round($bytes / 1_048_576, 2) . ' MB',
            $bytes >= 1_024     => round($bytes / 1_024, 2) . ' KB',
            default             => $bytes . ' B',
        };
    }

    public function existsOnDisk(): bool
    {
        return Storage::disk('private')->exists($this->file_path);
    }
}
