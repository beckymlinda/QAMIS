<?php

namespace App\Support;

class DefaultScoringRubric
{
    public const LEVELS = [
        4 => [
            'label' => 'Excellent',
            'descriptor' => 'Fully meets or exceeds the standard with comprehensive evidence.',
        ],
        3 => [
            'label' => 'Good',
            'descriptor' => 'Meets the standard with only minor gaps.',
        ],
        2 => [
            'label' => 'Satisfactory',
            'descriptor' => 'Meets minimum requirements but requires improvement.',
        ],
        1 => [
            'label' => 'Insufficient',
            'descriptor' => 'Partially meets the standard with significant deficiencies.',
        ],
        0 => [
            'label' => 'Poor/Unavailable',
            'descriptor' => 'Does not meet the standard or no evidence exists.',
        ],
    ];

    public static function levels(): array
    {
        return self::LEVELS;
    }
}
