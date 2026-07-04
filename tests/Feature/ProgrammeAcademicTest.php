<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\Programme;
use App\Models\StaffMember;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProgrammeAcademicTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsProgrammeManager(Institution $institution): User
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::create([
            'institution_id' => $institution->id,
            'name' => 'Programme Manager',
            'email' => 'manager@test.mw',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $user->givePermissionTo(Permission::findByName('programme.manage'));

        $this->actingAs($user);

        return $user;
    }

    public function test_lecturer_already_on_programme_shows_validation_error(): void
    {
        $institution = Institution::create(['name' => 'Test HEI', 'status' => 'active']);
        $programme = Programme::create([
            'institution_id' => $institution->id,
            'name' => 'Test Programme',
            'level' => 'undergraduate',
        ]);

        $this->actingAsProgrammeManager($institution);

        $existingUser = User::create([
            'institution_id' => $institution->id,
            'name' => 'Existing Lecturer',
            'email' => 'lecturer@test.mw',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $existingUser->assignRole('lecturer');

        StaffMember::create([
            'institution_id' => $institution->id,
            'programme_id' => $programme->id,
            'user_id' => $existingUser->id,
            'type' => 'academic',
            'name' => 'Existing Lecturer',
        ]);

        $this->from(route('programmes.academic.index', ['programme' => $programme, 'tab' => 'lecturers']))
            ->post(route('programmes.lecturers.store', $programme), [
                'tab' => 'lecturers',
                'name' => 'Existing Lecturer',
                'email' => 'lecturer@test.mw',
                'create_portal_login' => '1',
            ])
            ->assertRedirect(route('programmes.academic.index', ['programme' => $programme, 'tab' => 'lecturers']))
            ->assertSessionHasErrors('email');

        $this->assertSame(1, StaffMember::count());
    }

    public function test_student_email_cannot_be_used_for_lecturer_portal(): void
    {
        $institution = Institution::create(['name' => 'Test HEI', 'status' => 'active']);
        $programme = Programme::create([
            'institution_id' => $institution->id,
            'name' => 'Test Programme',
            'level' => 'undergraduate',
        ]);

        $this->actingAsProgrammeManager($institution);

        $studentUser = User::create([
            'institution_id' => $institution->id,
            'name' => 'Student User',
            'email' => 'student@test.mw',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $studentUser->assignRole('student');

        $this->from(route('programmes.academic.index', ['programme' => $programme, 'tab' => 'lecturers']))
            ->post(route('programmes.lecturers.store', $programme), [
                'tab' => 'lecturers',
                'name' => 'Should Fail',
                'email' => 'student@test.mw',
                'create_portal_login' => '1',
            ])
            ->assertSessionHasErrors('email');

        $this->assertSame(0, StaffMember::count());
    }

    public function test_existing_portal_user_can_be_linked_to_orphan_staff_record(): void
    {
        $institution = Institution::create(['name' => 'Test HEI', 'status' => 'active']);
        $programme = Programme::create([
            'institution_id' => $institution->id,
            'name' => 'Test Programme',
            'level' => 'undergraduate',
        ]);

        $orphan = StaffMember::create([
            'institution_id' => $institution->id,
            'programme_id' => $programme->id,
            'type' => 'academic',
            'name' => 'Dr Existing',
            'designation' => 'Lecturer',
        ]);

        $existingUser = User::create([
            'institution_id' => $institution->id,
            'name' => 'Dr Existing',
            'email' => 'existing@test.mw',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $this->actingAsProgrammeManager($institution);

        $this->post(route('programmes.lecturers.store', $programme), [
            'tab' => 'lecturers',
            'name' => 'Dr Existing',
            'email' => 'existing@test.mw',
            'create_portal_login' => '1',
        ])->assertRedirect(route('programmes.academic.index', ['programme' => $programme, 'tab' => 'lecturers']));

        $this->assertSame(1, StaffMember::count());
        $orphan->refresh();
        $this->assertSame($existingUser->id, $orphan->user_id);
        $this->assertTrue($existingUser->fresh()->isLecturer());
    }
}
