<?php

namespace App\Enums;

enum AssessmentStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Reviewed = 'reviewed';
    case Approved = 'approved';
    case Locked = 'locked';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Submitted',
            self::Reviewed => 'Reviewed',
            self::Approved => 'Approved',
            self::Locked => 'Locked',
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Draft => $next === self::Submitted,
            self::Submitted => in_array($next, [self::Reviewed, self::Draft], true),
            self::Reviewed => in_array($next, [self::Approved, self::Submitted], true),
            self::Approved => $next === self::Locked,
            self::Locked => false,
        };
    }
}
