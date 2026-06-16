<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (Model $model): void {
            static::writeAuditLog($model, 'created', null, $model->getAttributes());
        });

        static::updated(function (Model $model): void {
            static::writeAuditLog($model, 'updated', $model->getOriginal(), $model->getChanges());
        });

        static::deleted(function (Model $model): void
        {
            static::writeAuditLog($model, 'deleted', $model->getOriginal(), null);
        });
    }

    protected static function writeAuditLog(Model $model, string $event, ?array $old, ?array $new): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'institution_id' => $model->getAttribute('institution_id') ?? Auth::user()?->institution_id,
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'event' => $event,
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => request()?->ip(),
        ]);
    }
}
