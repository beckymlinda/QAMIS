<?php

namespace App\Models\Concerns;

use App\Models\Institution;
use App\Support\InstitutionContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToInstitution
{
    public static function bootBelongsToInstitution(): void
    {
        static::creating(function (Model $model): void {
            if (! $model->getAttribute('institution_id') && InstitutionContext::id()) {
                $model->setAttribute('institution_id', InstitutionContext::id());
            }
        });

        static::addGlobalScope('institution', function (Builder $builder): void {
            if ($institutionId = InstitutionContext::id()) {
                $builder->where($builder->getModel()->getTable().'.institution_id', $institutionId);
            }
        });
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }
}
