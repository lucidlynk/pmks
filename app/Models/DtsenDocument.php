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
    /**
     * Download file langsung via Storage tanpa route.
     * Gunakan ini untuk trigger download dari action Filament.
     */
    public function download(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = str_replace('/', '-', 'DTSEN-' . $this->dtsenRequest->reference_number . '.pdf');
        return Storage::disk('private')->download($this->file_path, $filename);
    }
    /**
     * Temporary URL untuk preview (hanya jika disk support, misal S3).
     * Untuk disk local/private, gunakan method download() langsung.
     */
    public function getDownloadUrl(): string
    {
        // Disk private lokal tidak support temporary URL.
        // Download dilakukan via Filament action, bukan via URL langsung.
        // Method ini disimpan untuk kompatibilitas masa depan (misal migrasi ke S3).
        return '';
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
