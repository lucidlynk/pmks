<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DinasSurat extends Model
{
    use SoftDeletes;

    protected $table = 'dinas_surats';

    protected $fillable = [
        'judul',
        'nomor_surat',
        'tanggal_surat',
        'kategori',
        'deskripsi',
        'file_path',
        'original_filename',
        'file_size',
        'target_scope',
        'kecamatan_ids',
        'is_active',
        'uploaded_by',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'kecamatan_ids' => 'array',
        'is_active'     => 'boolean',
        'file_size'     => 'integer',
    ];

    // ================================================================
    // RELATIONSHIPS
    // ================================================================

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // ================================================================
    // SCOPES
    // ================================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForKecamatan($query, int $kecamatanId)
    {
        return $query->where(function ($q) use ($kecamatanId) {
            $q->where('target_scope', 'semua')
              ->orWhere(function ($q2) use ($kecamatanId) {
                  $q2->where('target_scope', 'kecamatan')
                     ->whereJsonContains('kecamatan_ids', $kecamatanId);
              });
        });
    }

    // ================================================================
    // HELPERS
    // ================================================================

    public function getKategoriLabelAttribute(): string
    {
        return match ($this->kategori) {
            'edaran'     => 'Surat Edaran',
            'sk'         => 'Surat Keputusan',
            'pengumuman' => 'Pengumuman',
            'lainnya'    => 'Lainnya',
            default      => $this->kategori,
        };
    }

    public function getKategoriColorAttribute(): string
    {
        return match ($this->kategori) {
            'edaran'     => 'info',
            'sk'         => 'warning',
            'pengumuman' => 'success',
            'lainnya'    => 'gray',
            default      => 'gray',
        };
    }

    public function getFileSizeLabelAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }

    public static function kategoriOptions(): array
    {
        return [
            'edaran'     => 'Surat Edaran',
            'sk'         => 'Surat Keputusan',
            'pengumuman' => 'Pengumuman',
            'lainnya'    => 'Lainnya',
        ];
    }

    public function isVisibleTo(User $user): bool
    {
        if ($this->target_scope === 'semua') return true;

        if ($user->isOperatorDesa() && $user->village?->kecamatan_id) {
            return in_array($user->village->kecamatan_id, $this->kecamatan_ids ?? []);
        }

        return true;
    }
}
