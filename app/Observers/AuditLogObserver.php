<?php

namespace App\Observers;

use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;

class AuditLogObserver
{
    public function created(Model $model): void
    {
        AuditLogService::logCreate($model);
    }

    public function updated(Model $model): void
    {
        $oldValues = array_intersect_key(
            $model->getOriginal(),
            $model->getDirty()
        );

        AuditLogService::logUpdate($model, $oldValues);
    }

    public function deleted(Model $model): void
    {
        AuditLogService::logDelete($model);
    }
}
