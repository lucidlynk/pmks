<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    // Audit log tidak boleh diubah atau dihapus
    public $timestamps = false;
    public $incrementing = true;

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values'  => 'array',
        'new_values'  => 'array',
        'created_at'  => 'datetime',
    ];

    // Override: tidak bisa update
    public function save(array $options = []): bool
    {
        if ($this->exists) {
            return false;
        }

        return parent::save($options);
    }

    // Override: tidak bisa delete
    public function delete(): bool
    {
        return false;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}