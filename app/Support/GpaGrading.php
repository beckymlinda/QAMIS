<?php

namespace App\Support;

class GpaGrading
{
    /**
     * Grade bands from Assessment Rules and Regulations — Table 1.
     *
     * @return array{letter: string, points: float, quality: string, decision: string}
     */
    public static function fromPercentage(float $percentage): array
    {
        $mark = max(0, min(100, $percentage));

        return match (true) {
            $mark >= 85 => ['letter' => 'A+', 'points' => 4.00, 'quality' => 'Excellent', 'decision' => 'High Distinction'],
            $mark >= 75 => ['letter' => 'A', 'points' => 3.75, 'quality' => 'Excellent', 'decision' => 'Distinction'],
            $mark >= 70 => ['letter' => 'B+', 'points' => 3.74, 'quality' => 'Above average', 'decision' => 'High Credit'],
            $mark >= 65 => ['letter' => 'B', 'points' => 3.00, 'quality' => 'Above average', 'decision' => 'Credit'],
            $mark >= 60 => ['letter' => 'C+', 'points' => 2.99, 'quality' => 'Average', 'decision' => 'High Pass'],
            $mark >= 55 => ['letter' => 'C', 'points' => 2.50, 'quality' => 'Average', 'decision' => 'Satisfactory Pass'],
            $mark >= 50 => ['letter' => 'C-', 'points' => 2.00, 'quality' => 'Average', 'decision' => 'Bare Pass'],
            $mark >= 45 => ['letter' => 'D', 'points' => 1.99, 'quality' => 'Fail', 'decision' => 'Marginal Failure'],
            $mark >= 40 => ['letter' => 'E', 'points' => 1.00, 'quality' => 'Fail', 'decision' => 'Failure'],
            default => ['letter' => 'F', 'points' => 0.00, 'quality' => 'Fail', 'decision' => 'Undoubted Failure'],
        };
    }

    public static function computeFinal(?float $coursework, ?float $exam, float $courseworkWeight = 0.4): ?float
    {
        if ($coursework === null && $exam === null) {
            return null;
        }

        if ($coursework === null) {
            return $exam;
        }

        if ($exam === null) {
            return $coursework;
        }

        $examWeight = 1 - $courseworkWeight;

        return round(($coursework * $courseworkWeight) + ($exam * $examWeight), 2);
    }

    public static function hasPassed(string $letterGrade): bool
    {
        return ! in_array($letterGrade, ['D', 'E', 'F'], true);
    }
}
