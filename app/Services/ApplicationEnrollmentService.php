<?php

namespace App\Services;

use App\Enums\ProgrammeApplicationStatus;
use App\Models\ProgrammeApplication;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ApplicationEnrollmentService
{
    public function __construct(
        protected StudentManagementService $students,
    ) {}

    public function enroll(ProgrammeApplication $application, ?int $yearOfStudy = 1, bool $allowWithoutApproval = false): Student
    {
        $application->loadMissing(['user', 'institution', 'enrolledStudent']);

        if ($application->enrolledStudent) {
            $this->promoteApplicantToStudent($application->user, $application);

            return $application->enrolledStudent;
        }

        abort_unless(
            $application->canBeEnrolled() || ($allowWithoutApproval && $application->needsStudentRecord()),
            422,
            'This application cannot be enrolled.'
        );

        return DB::transaction(function () use ($application, $yearOfStudy) {
            $user = $application->user;
            $institution = $application->institution;

            $studentNumber = $this->students->generateStudentNumber($institution);

            $student = Student::create([
                'institution_id' => $institution->id,
                'user_id' => $user->id,
                'programme_id' => $application->programme_id,
                'student_number' => $studentNumber,
                'first_name' => $application->first_name,
                'last_name' => $application->last_name,
                'email' => $application->email,
                'phone' => $application->phone,
                'year_of_study' => $yearOfStudy,
                'status' => 'active',
            ]);

            $this->promoteApplicantToStudent($user, $application);

            $application->update([
                'status' => ProgrammeApplicationStatus::Enrolled,
                'enrolled_at' => now(),
                'enrolled_student_id' => $student->id,
            ]);

            return $student;
        });
    }

    public function promoteApplicantToStudent(User $user, ProgrammeApplication $application): void
    {
        $user->update([
            'name' => $application->fullName(),
            'institution_id' => $application->institution_id,
        ]);

        if ($user->hasRole('applicant')) {
            $user->removeRole('applicant');
        }

        if (! $user->hasRole('student')) {
            $user->assignRole('student');
        }
    }

    public function createApplicantUser(array $data): User
    {
        $user = User::create([
            'institution_id' => $data['institution_id'],
            'name' => trim($data['first_name'].' '.$data['last_name']),
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_active' => true,
        ]);

        $user->assignRole('applicant');

        return $user;
    }
}
