<?php

namespace App\Enums;

enum AccreditationRecommendation: string
{
    case Ready = 'ready_for_accreditation';
    case WithConditions = 'accreditation_with_conditions';
    case Deferred = 'deferred_pending_improvements';
    case NotReady = 'not_ready_for_accreditation';

    public function label(): string
    {
        return match ($this) {
            self::Ready => 'Ready for Accreditation',
            self::WithConditions => 'Accreditation with Conditions',
            self::Deferred => 'Deferred Pending Improvements',
            self::NotReady => 'Not Ready for Accreditation',
        };
    }
}
