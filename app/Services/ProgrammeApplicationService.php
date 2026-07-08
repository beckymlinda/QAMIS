<?php

namespace App\Services;

use App\Enums\ProgrammeApplicationStatus;
use App\Models\Institution;
use App\Models\ProgrammeApplication;
use Illuminate\Support\Str;

class ProgrammeApplicationService
{
    public function generateApplicationNumber(Institution $institution): string
    {
        $prefix = Str::upper(Str::substr($institution->acronym ?: 'APP', 0, 6));
        $year = date('Y');
        $sequence = ProgrammeApplication::query()
            ->where('institution_id', $institution->id)
            ->where('application_number', 'like', "{$prefix}-{$year}-%")
            ->count() + 1;

        return sprintf('%s-%s-%04d', $prefix, $year, $sequence);
    }

    public function storeUpload(ProgrammeApplication $application, $file, string $folder): string
    {
        return $file->store(
            'institutions/'.$application->institution_id.'/applications/'.$application->id.'/'.$folder,
            'local'
        );
    }

    public function deleteUpload(?string $path): void
    {
        if ($path && \Illuminate\Support\Facades\Storage::disk('local')->exists($path)) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($path);
        }
    }

    /** @return array<int, array{label: string, date: ?\Illuminate\Support\Carbon, note: ?string}> */
    public function timeline(ProgrammeApplication $application): array
    {
        $steps = [
            ['label' => 'Application submitted', 'date' => $application->submitted_at, 'note' => null],
            ['label' => 'Payment verified', 'date' => $application->payment_verified_at, 'note' => null],
            ['label' => 'Under review', 'date' => $application->status === ProgrammeApplicationStatus::UnderReview ? $application->updated_at : null, 'note' => null],
            ['label' => 'Decision made', 'date' => $application->decision_at, 'note' => $application->admin_notes],
            ['label' => 'Enrolled', 'date' => $application->enrolled_at, 'note' => null],
        ];

        return $steps;
    }
}
