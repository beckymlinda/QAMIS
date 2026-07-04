<?php

namespace App\Support;

class GovernanceBodyType
{
    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'board_of_trustees' => 'Board of Trustees or equivalent',
            'council' => 'Council Members',
            'management' => 'HEI Management',
            'senate' => 'HEI Senate',
        ];
    }

    public static function label(string $type): string
    {
        return self::labels()[$type] ?? ucfirst(str_replace('_', ' ', $type));
    }
}
