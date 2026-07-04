<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseOffering;
use App\Models\EvaluationPeriod;
use App\Models\EvaluationQuestion;
use App\Models\Institution;
use App\Models\Programme;
use App\Models\Student;
use App\Models\StudentCourseEnrolment;
use App\Models\TeachingEvaluation;
use App\Models\TeachingEvaluationResponse;
use App\Models\User;
use Database\Seeders\EvaluationQuestionnaireSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class TeachingEvaluationReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_view_evaluation_period_and_download_pdf_report(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(EvaluationQuestionnaireSeeder::class);

        $institution = Institution::create(['name' => 'Test HEI', 'status' => 'active']);
        $programme = Programme::create([
            'institution_id' => $institution->id,
            'name' => 'Business Administration',
            'level' => 'undergraduate',
        ]);

        $manager = User::create([
            'institution_id' => $institution->id,
            'name' => 'Programme Manager',
            'email' => 'manager@test.mw',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $manager->givePermissionTo(Permission::findByName('programme.manage'));

        $course = Course::create([
            'institution_id' => $institution->id,
            'programme_id' => $programme->id,
            'code' => 'BUSI-1101',
            'title' => 'Intro to Business',
            'credit_hours' => 3,
        ]);

        $offering = CourseOffering::create([
            'institution_id' => $institution->id,
            'course_id' => $course->id,
            'academic_year' => '2026/2027',
            'semester' => 1,
            'delivery_mode' => 'face_to_face',
        ]);

        $studentUser = User::create([
            'institution_id' => $institution->id,
            'name' => 'Test Student',
            'email' => 'student@test.mw',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $studentUser->assignRole('student');

        $student = Student::create([
            'institution_id' => $institution->id,
            'user_id' => $studentUser->id,
            'programme_id' => $programme->id,
            'student_number' => 'STU001',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'student@test.mw',
            'year_of_study' => 1,
        ]);

        StudentCourseEnrolment::create([
            'student_id' => $student->id,
            'course_offering_id' => $offering->id,
        ]);

        $period = EvaluationPeriod::create([
            'institution_id' => $institution->id,
            'academic_year' => '2026/2027',
            'semester' => 1,
            'title' => 'End of Semester Teaching Evaluation',
            'opens_at' => now()->subDay(),
            'closes_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        $evaluation = TeachingEvaluation::create([
            'student_id' => $student->id,
            'course_offering_id' => $offering->id,
            'evaluation_period_id' => $period->id,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $likertQuestion = EvaluationQuestion::where('question_type', 'likert_5')->first();
        $textQuestion = EvaluationQuestion::where('question_type', 'text')->first();

        TeachingEvaluationResponse::create([
            'teaching_evaluation_id' => $evaluation->id,
            'evaluation_question_id' => $likertQuestion->id,
            'rating' => 5,
        ]);

        TeachingEvaluationResponse::create([
            'teaching_evaluation_id' => $evaluation->id,
            'evaluation_question_id' => $textQuestion->id,
            'response_text' => 'Great course content and lecturer support.',
        ]);

        $this->actingAs($manager)
            ->get(route('programmes.evaluation-periods.show', [$programme, $period]))
            ->assertOk()
            ->assertSee('Evaluation of Teaching Questionnaire')
            ->assertSee('BUSI-1101')
            ->assertSee('Great course content and lecturer support.');

        $this->actingAs($manager)
            ->get(route('programmes.evaluation-periods.report', [$programme, $period]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
