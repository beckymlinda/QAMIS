<?php

namespace App\Services;

use App\Models\Institution;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Str;

class StudentManagementService
{
    public const DEFAULT_PASSWORD = 'password';

    public function generateStudentNumber(Institution $institution): string
    {
        $prefix = Str::upper(Str::substr($institution->acronym ?: 'STU', 0, 6));
        $year = date('Y');
        $sequence = Student::query()
            ->where('institution_id', $institution->id)
            ->where('student_number', 'like', "{$prefix}-{$year}-%")
            ->count() + 1;

        return sprintf('%s-%s-%04d', $prefix, $year, $sequence);
    }

    public function exampleEmail(Institution $institution): string
    {
        $domain = Str::slug($institution->acronym ?: $institution->name, '');

        return "firstname@{$domain}.com";
    }

    public function defaultEmail(Institution $institution, string $firstName, ?string $lastName = null): string
    {
        $domain = Str::slug($institution->acronym ?: $institution->name, '');
        $local = Str::slug(Str::lower($firstName), '');

        if ($local === '') {
            $local = 'student';
        }

        $email = "{$local}@{$domain}.com";

        if (! User::query()->where('institution_id', $institution->id)->where('email', $email)->exists()
            && ! Student::query()->where('institution_id', $institution->id)->where('email', $email)->exists()) {
            return $email;
        }

        $suffix = 1;
        while (true) {
            $candidate = "{$local}{$suffix}@{$domain}.com";
            if (! User::query()->where('institution_id', $institution->id)->where('email', $candidate)->exists()
                && ! Student::query()->where('institution_id', $institution->id)->where('email', $candidate)->exists()) {
                return $candidate;
            }
            $suffix++;
        }
    }

    public function resolveStudentNumber(Institution $institution, ?string $studentNumber): string
    {
        return filled(trim((string) $studentNumber))
            ? trim($studentNumber)
            : $this->generateStudentNumber($institution);
    }

    public function resolveEmail(Institution $institution, ?string $email, string $firstName, string $lastName): string
    {
        if (filled(trim((string) $email))) {
            return Str::lower(trim($email));
        }

        return $this->defaultEmail($institution, $firstName, $lastName);
    }
}
