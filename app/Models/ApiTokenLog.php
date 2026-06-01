<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiTokenLog extends Model
{
    public $timestamps = false;

    protected $table = 'api_token_logs';

    protected $fillable = [
        'api_client_id',
        'endpoint',
        'method',
        'parameters',
        'response_code',
        'ip_address',
        'user_agent',
        'accessed_at',
    ];

    protected $casts = [
        'parameters'  => 'array',
        'accessed_at' => 'datetime',
    ];

    // ================================================================
    // RELATIONSHIPS
    // ================================================================

    public function apiClient(): BelongsTo
    {
        return $this->belongsTo(ApiClient::class, 'api_client_id');
    }

    // ================================================================
    // SCOPES
    // ================================================================

    public function scopeToday($query)
    {
        return $query->whereDate('accessed_at', today());
    }

    public function scopeForEndpoint($query, string $endpoint)
    {
        return $query->where('endpoint', $endpoint);
    }
}
