<?php

namespace App\Enums;

enum FeePaymentStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Awaiting approval',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-100 text-amber-900 ring-amber-200',
            self::Approved => 'bg-green-100 text-green-800 ring-green-200',
            self::Rejected => 'bg-red-100 text-red-800 ring-red-200',
        };
    }
}
