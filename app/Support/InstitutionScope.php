<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class InstitutionScope
{
    public static function institutionId(?User $user = null): ?int
    {
        $user ??= auth()->user();

        if (! $user) {
            return null;
        }

        if ($user->isNcheOrSystemAdmin()) {
            return InstitutionContext::id();
        }

        return $user->institution_id;
    }

    public static function apply(Builder $query, ?User $user = null, string $column = 'institution_id'): Builder
    {
        $user ??= auth()->user();
        $institutionId = static::institutionId($user);

        if ($institutionId) {
            return $query->where($column, $institutionId);
        }

        if ($user?->isNcheOrSystemAdmin()) {
            return $query;
        }

        return $query->whereRaw('1 = 0');
    }
}
