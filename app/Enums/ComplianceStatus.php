<?php

namespace App\Enums;

enum ComplianceStatus: string
{
    case FullyCompliant = 'fully_compliant';
    case PartiallyCompliant = 'partially_compliant';
    case NonCompliant = 'non_compliant';

    public function label(): string
    {
        return match ($this) {
            self::FullyCompliant => 'Fully Compliant',
            self::PartiallyCompliant => 'Partially Compliant',
            self::NonCompliant => 'Non-Compliant',
        };
    }
}
