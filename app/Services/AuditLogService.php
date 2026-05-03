<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    public static function log(
        string $action,
        ?Model $model = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): void {
        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id'   => $model?->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    public static function logLogin(bool $success, string $email): void
    {
        AuditLog::create([
            'user_id'    => $success ? Auth::id() : null,
            'action'     => $success ? 'login' : 'login_failed',
            'model_type' => null,
            'model_id'   => null,
            'old_values' => null,
            'new_values' => ['email' => $email],
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    public static function logCreate(Model $model): void
    {
        static::log(
            action: 'create',
            model: $model,
            newValues: $model->toArray(),
        );
    }

    public static function logUpdate(Model $model, array $oldValues): void
    {
        static::log(
            action: 'update',
            model: $model,
            oldValues: $oldValues,
            newValues: $model->toArray(),
        );
    }

    public static function logDelete(Model $model): void
    {
        static::log(
            action: 'delete',
            model: $model,
            oldValues: $model->toArray(),
        );
    }

    public static function logResetPassword(Model $user): void
    {
        static::log(
            action: 'reset_password',
            model: $user,
        );
    }
}
