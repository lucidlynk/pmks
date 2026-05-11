<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DtsenDocument extends Model
{
    protected $fillable = [
        'dtsen_request_id',
        'file_path',
        'original_filename',
        'file_size',
        'is_current',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'is_current' => 'boolean',
            'file_size'  => 'integer',
        ];
    }

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function dtsenRequest(): BelongsTo
    {
        return $this->belongsTo(DtsenRequest::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // -------------------------------------------------------------------------
    // Business logic
    // -------------------------------------------------------------------------

    /**
     * Set dokumen ini sebagai current, non-aktifkan yang lama.
     * Dipanggil dalam DB::transaction dari service/action.
     */
    public function setAsCurrent(): void
    {
        DtsenDocument::where('dtsen_request_id', $this->dtsen_request_id)
            ->where('id', '!=', $this->id)
            ->update(['is_current' => false]);

        $this->update(['is_current' => true]);
    }

    public function fileSizeForHumans(): string
    {
        $kb = $this->file_size / 1024;
        if ($kb < 1024) {
            return round($kb, 1) . ' KB';
        }
        return round($kb / 1024, 2) . ' MB';
    }

    public function storageUrl(): string
    {
        return Storage::disk('private')->temporaryUrl(
            $this->file_path,
            now()->addMinutes(5)
        );
    }
}
