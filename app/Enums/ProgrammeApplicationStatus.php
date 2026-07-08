<?php

namespace App\Enums;

enum ProgrammeApplicationStatus: string
{
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case Reviewed = 'reviewed';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case WaitingList = 'waiting_list';
    case Enrolled = 'enrolled';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::UnderReview => 'Under Review',
            self::Reviewed => 'Reviewed',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::WaitingList => 'Waiting List',
            self::Enrolled => 'Enrolled',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status) => [$status->value => $status->label()])
            ->all();
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Submitted => 'bg-blue-100 text-blue-800 ring-blue-200',
            self::UnderReview => 'bg-amber-100 text-amber-900 ring-amber-200',
            self::Reviewed => 'bg-purple-100 text-purple-800 ring-purple-200',
            self::Approved => 'bg-green-100 text-green-800 ring-green-200',
            self::Rejected => 'bg-red-100 text-red-800 ring-red-200',
            self::WaitingList => 'bg-orange-100 text-orange-800 ring-orange-200',
            self::Enrolled => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
        };
    }
}
