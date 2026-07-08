<?php

namespace App\Support;

class CertificateGrades
{
    /** @return list<string> */
    public static function coreSubjects(): array
    {
        return [
            'English',
            'Mathematics',
            'Chichewa',
            'Biology',
            'Agriculture',
            'Chemistry',
        ];
    }

    /** @return list<string> */
    public static function optionalSubjects(): array
    {
        return [
            'Life Skills',
            'Geography',
            'Social Studies',
        ];
    }

    /** @return array<string, int|null> */
    public static function defaultGradeMap(): array
    {
        return array_fill_keys(self::coreSubjects(), null);
    }

    /**
     * @param  array<string, mixed>  $input
     * @return list<array{subject: string, points: int}>
     */
    public static function parseFromRequest(array $input): array
    {
        $grades = [];
        $core = $input['grades'] ?? [];

        foreach (self::coreSubjects() as $subject) {
            $points = $core[$subject] ?? null;
            if ($points !== null && $points !== '') {
                $grades[] = [
                    'subject' => $subject,
                    'points' => (int) $points,
                ];
            }
        }

        foreach ($input['extra_grades'] ?? [] as $row) {
            $subject = trim((string) ($row['subject'] ?? ''));
            $points = $row['points'] ?? null;

            if ($subject === '' || $points === null || $points === '') {
                continue;
            }

            $grades[] = [
                'subject' => $subject,
                'points' => (int) $points,
            ];
        }

        return $grades;
    }

    /** @return array<string, string> */
    public static function validationRules(): array
    {
        $rules = [];

        foreach (self::coreSubjects() as $subject) {
            $rules['grades.'.$subject] = 'required|integer|min:1|max:9';
        }

        $rules['extra_grades'] = 'nullable|array|max:10';
        $rules['extra_grades.*.subject'] = 'required_with:extra_grades.*.points|string|max:100';
        $rules['extra_grades.*.points'] = 'nullable|integer|min:1|max:9';

        return $rules;
    }

    /**
     * @param  list<array{subject: string, points: int}>|null  $stored
     * @return array{core: array<string, int|null>, extra: list<array{subject: string, points: int}>}
     */
    public static function forForm(?array $stored): array
    {
        $core = self::defaultGradeMap();
        $extra = [];

        foreach ($stored ?? [] as $row) {
            $subject = $row['subject'] ?? '';
            $points = isset($row['points']) ? (int) $row['points'] : null;

            if (array_key_exists($subject, $core)) {
                $core[$subject] = $points;
            } else {
                $extra[] = ['subject' => $subject, 'points' => $points];
            }
        }

        return compact('core', 'extra');
    }

    /**
     * @param  list<array{subject: string, points: int}>|null  $stored
     */
    public static function totalPoints(?array $stored): int
    {
        return collect($stored ?? [])->sum(fn ($row) => (int) ($row['points'] ?? 0));
    }
}
