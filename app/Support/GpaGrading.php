<?php

namespace App\Support;

class GpaGrading
{
    public const COURSEWORK_WEIGHT = 0.4;

    public const EXAM_WEIGHT = 0.6;

    public static function courseworkPortionPercent(): float
    {
        return self::COURSEWORK_WEIGHT * 100;
    }

    public static function examPortionPercent(): float
    {
        return self::EXAM_WEIGHT * 100;
    }
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

    public static function computeFinal(?float $coursework, ?float $exam, float $courseworkWeight = self::COURSEWORK_WEIGHT): ?float
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

    /**
     * Final mark from weighted coursework points (out of 40) plus exam score (0–100).
     */
    public static function computeFinalFromContributions(float $earnedCourseworkPoints, ?float $examPercentage): ?float
    {
        if ($examPercentage === null) {
            return null;
        }

        return round($earnedCourseworkPoints + ($examPercentage * self::EXAM_WEIGHT), 2);
    }

    public static function hasPassed(string $letterGrade): bool
    {
        return ! in_array($letterGrade, ['D', 'E', 'F'], true);
    }

    /**
     * Visual tone for grade points: fail (red), warning (amber), success (green).
     */
    public static function toneForPoints(float $points): string
    {
        if ($points < 2.0) {
            return 'fail';
        }

        if ($points < 3.0) {
            return 'warning';
        }

        return 'success';
    }

    public static function toneForLetter(string $letterGrade): string
    {
        if (! self::hasPassed($letterGrade)) {
            return 'fail';
        }

        $points = self::fromPercentage(self::midpointForLetter($letterGrade))['points'];

        return self::toneForPoints($points);
    }

    /**
     * @return array{gpa: float, passed: bool, label: string, tone: string, description: string}|null
     */
    public static function semesterStanding(?float $gpa): ?array
    {
        if ($gpa === null) {
            return null;
        }

        $passed = $gpa >= 2.0;
        $tone = self::toneForPoints($gpa);

        return [
            'gpa' => $gpa,
            'passed' => $passed,
            'label' => $passed ? 'Pass' : 'Fail',
            'tone' => $tone,
            'description' => $passed
                ? 'Semester GPA meets the minimum pass threshold (2.00).'
                : 'Semester GPA is below the minimum pass threshold (2.00).',
        ];
    }

    /** Approximate percentage midpoint for letter-grade tone lookup. */
    protected static function midpointForLetter(string $letter): float
    {
        return match ($letter) {
            'A+' => 90,
            'A' => 80,
            'B+' => 72.5,
            'B' => 67.5,
            'C+' => 62.5,
            'C' => 57.5,
            'C-' => 52.5,
            'D' => 47.5,
            'E' => 42.5,
            default => 20,
        };
    }

    /**
     * Tailwind class sets for grade tone badges and cells.
     *
     * @return array{badge: string, text: string, ring: string, bg: string}
     */
    public static function toneClasses(string $tone): array
    {
        return match ($tone) {
            'fail' => [
                'badge' => 'bg-red-100 text-red-800 ring-red-200',
                'text' => 'text-red-700',
                'ring' => 'ring-red-200',
                'bg' => 'bg-red-50',
            ],
            'warning' => [
                'badge' => 'bg-amber-100 text-amber-900 ring-amber-200',
                'text' => 'text-amber-800',
                'ring' => 'ring-amber-200',
                'bg' => 'bg-amber-50',
            ],
            default => [
                'badge' => 'bg-green-100 text-green-800 ring-green-200',
                'text' => 'text-green-700',
                'ring' => 'ring-green-200',
                'bg' => 'bg-green-50',
            ],
        };
    }

    /**
     * Semester GPA: sum(grade points × credit hours) / sum(credit hours).
     */
    public static function semesterGpaFromResults(iterable $results): ?float
    {
        $points = 0.0;
        $credits = 0.0;

        foreach ($results as $result) {
            if ($result->grade_points === null) {
                continue;
            }

            $creditHours = (float) ($result->enrolment?->courseOffering?->course?->credit_hours ?? 0);

            if ($creditHours <= 0) {
                continue;
            }

            $points += (float) $result->grade_points * $creditHours;
            $credits += $creditHours;
        }

        return $credits > 0 ? round($points / $credits, 2) : null;
    }
}
