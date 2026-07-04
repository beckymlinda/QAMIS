<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseOffering;
use App\Models\Institution;
use App\Models\Programme;
use App\Models\StaffMember;
use App\Models\Student;
use App\Models\StudentCourseEnrolment;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LecturerPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_lecturer_can_view_portal_and_courses(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $institution = Institution::create(['name' => 'Test HEI', 'status' => 'active']);
        $programme = Programme::create([
            'institution_id' => $institution->id,
            'name' => 'Test Programme',
            'level' => 'undergraduate',
        ]);

        $user = User::create([
            'institution_id' => $institution->id,
            'name' => 'Dr Test',
            'email' => 'lecturer@test.mw',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $user->assignRole('lecturer');

        $staff = StaffMember::create([
            'institution_id' => $institution->id,
            'programme_id' => $programme->id,
            'user_id' => $user->id,
            'type' => 'academic',
            'name' => 'Dr Test',
        ]);

        $course = Course::create([
            'institution_id' => $institution->id,
            'programme_id' => $programme->id,
            'code' => 'TEST-101',
            'title' => 'Test Course',
            'credit_hours' => 3,
        ]);

        CourseOffering::create([
            'institution_id' => $institution->id,
            'course_id' => $course->id,
            'staff_member_id' => $staff->id,
            'academic_year' => '2026/2027',
            'semester' => 1,
            'delivery_mode' => 'face_to_face',
        ]);

        $this->actingAs($user)->get(route('lecturer.dashboard'))->assertOk()->assertSee('Lecturer Portal');
        $this->actingAs($user)->get(route('lecturer.courses'))->assertOk()->assertSee('TEST-101');
        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('lecturer.dashboard'));
    }
}
