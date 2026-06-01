<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiClient extends Model
{
    use SoftDeletes;

    protected $table = 'api_clients';

    protected $fillable = [
        'nama_instansi',
        'keterangan',
        'token_id',
        'token_preview',
        'is_active',
        'created_by',
        'last_used_at',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'last_used_at' => 'datetime',
    ];

    // ================================================================
    // RELATIONSHIPS
    // ================================================================

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ApiTokenLog::class, 'api_client_id');
    }

    public function accessToken(): BelongsTo
    {
        return $this->belongsTo(\Laravel\Sanctum\PersonalAccessToken::class, 'token_id');
    }

    // ================================================================
    // SCOPES
    // ================================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ================================================================
    // HELPERS
    // ================================================================

    public function getStatusLabelAttribute(): string
    {
        return $this->is_active ? 'Aktif' : 'Nonaktif';
    }

    public function getStatusColorAttribute(): string
    {
        return $this->is_active ? 'success' : 'danger';
    }

    public function getTotalRequestsAttribute(): int
    {
        return $this->logs()->count();
    }

    public function getRequestsTodayAttribute(): int
    {
        return $this->logs()
            ->whereDate('accessed_at', today())
            ->count();
    }
}
