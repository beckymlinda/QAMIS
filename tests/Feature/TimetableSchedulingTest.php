<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseOffering;
use App\Models\Institution;
use App\Models\Programme;
use App\Models\StaffMember;
use App\Models\TimetableSlot;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class TimetableSchedulingTest extends TestCase
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

    protected function createProgrammeFixture(): array
    {
        $institution = Institution::create(['name' => 'Test HEI', 'status' => 'active']);
        $programme = Programme::create([
            'institution_id' => $institution->id,
            'name' => 'Test Programme',
            'level' => 'undergraduate',
        ]);

        $lecturer = StaffMember::create([
            'institution_id' => $institution->id,
            'programme_id' => $programme->id,
            'type' => 'academic',
            'name' => 'Dr One',
        ]);

        $courseA = Course::create([
            'institution_id' => $institution->id,
            'programme_id' => $programme->id,
            'code' => 'BUSI-1101',
            'title' => 'Course A',
            'credit_hours' => 3,
        ]);

        $courseB = Course::create([
            'institution_id' => $institution->id,
            'programme_id' => $programme->id,
            'code' => 'BUSI-1102',
            'title' => 'Course B',
            'credit_hours' => 3,
        ]);

        $offeringA = CourseOffering::create([
            'institution_id' => $institution->id,
            'course_id' => $courseA->id,
            'staff_member_id' => $lecturer->id,
            'academic_year' => '2026/2027',
            'semester' => 1,
            'delivery_mode' => 'face_to_face',
        ]);

        $offeringB = CourseOffering::create([
            'institution_id' => $institution->id,
            'course_id' => $courseB->id,
            'staff_member_id' => $lecturer->id,
            'academic_year' => '2026/2027',
            'semester' => 1,
            'delivery_mode' => 'face_to_face',
        ]);

        $room = Classroom::create([
            'institution_id' => $institution->id,
            'code' => 'LR-101',
            'name' => 'Lecture Room 101',
            'capacity' => 40,
            'room_type' => 'lecture',
        ]);

        return compact('institution', 'programme', 'offeringA', 'offeringB', 'room', 'lecturer');
    }

    public function test_manual_slot_rejects_same_room_time_conflict(): void
    {
        $data = $this->createProgrammeFixture();
        $this->actingAsProgrammeManager($data['institution']);

        TimetableSlot::create([
            'course_offering_id' => $data['offeringA']->id,
            'classroom_id' => $data['room']->id,
            'day_of_week' => 1,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'session_type' => 'lecture',
        ]);

        $this->from(route('programmes.academic.index', ['programme' => $data['programme'], 'tab' => 'timetable']))
            ->post(route('programmes.timetable-slots.store', $data['programme']), [
                'tab' => 'timetable',
                'course_offering_id' => $data['offeringB']->id,
                'classroom_id' => $data['room']->id,
                'day_of_week' => 1,
                'start_time' => '09:00',
                'end_time' => '11:00',
                'session_type' => 'lecture',
            ])
            ->assertSessionHasErrors('start_time');

        $this->assertSame(1, TimetableSlot::count());
    }

    public function test_manual_slot_rejects_lecturer_time_conflict(): void
    {
        $data = $this->createProgrammeFixture();
        $this->actingAsProgrammeManager($data['institution']);

        $roomB = Classroom::create([
            'institution_id' => $data['institution']->id,
            'code' => 'LR-102',
            'name' => 'Lecture Room 102',
            'capacity' => 40,
            'room_type' => 'lecture',
        ]);

        TimetableSlot::create([
            'course_offering_id' => $data['offeringA']->id,
            'classroom_id' => $data['room']->id,
            'day_of_week' => 2,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'session_type' => 'lecture',
        ]);

        $this->post(route('programmes.timetable-slots.store', $data['programme']), [
            'tab' => 'timetable',
            'course_offering_id' => $data['offeringB']->id,
            'classroom_id' => $roomB->id,
            'day_of_week' => 2,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'session_type' => 'lecture',
        ])->assertSessionHasErrors('start_time');
    }

    public function test_auto_generate_builds_conflict_free_timetable(): void
    {
        $data = $this->createProgrammeFixture();
        $this->actingAsProgrammeManager($data['institution']);

        Classroom::create([
            'institution_id' => $data['institution']->id,
            'code' => 'LR-102',
            'name' => 'Lecture Room 102',
            'capacity' => 40,
            'room_type' => 'lecture',
        ]);

        $this->post(route('programmes.timetable.auto-generate', $data['programme']), [
            'tab' => 'timetable',
            'day_start' => '08:00',
            'day_end' => '17:00',
            'replace_existing' => '1',
        ])->assertRedirect(route('programmes.academic.index', ['programme' => $data['programme'], 'tab' => 'timetable']));

        $this->assertSame(2, TimetableSlot::count());

        $usedDays = TimetableSlot::query()->pluck('day_of_week')->unique()->sort()->values()->all();
        $this->assertGreaterThanOrEqual(2, count($usedDays), 'Offerings should be spread across multiple weekdays');

        $slots = TimetableSlot::with('courseOffering')->get();
        foreach ($slots as $left) {
            foreach ($slots as $right) {
                if ($left->id >= $right->id) {
                    continue;
                }

                if ((int) $left->day_of_week !== (int) $right->day_of_week) {
                    continue;
                }

                $overlap = $left->start_time < $right->end_time && $right->start_time < $left->end_time;
                if (! $overlap) {
                    continue;
                }

                $this->assertNotSame($left->classroom_id, $right->classroom_id, 'Room conflict in generated timetable');
                $this->assertNotSame(
                    $left->courseOffering?->staff_member_id,
                    $right->courseOffering?->staff_member_id,
                    'Lecturer conflict in generated timetable'
                );
            }
        }
    }

    public function test_regenerate_rotates_day_allocation(): void
    {
        $data = $this->createProgrammeFixture();
        $this->actingAsProgrammeManager($data['institution']);

        Classroom::create([
            'institution_id' => $data['institution']->id,
            'code' => 'LR-102',
            'name' => 'Lecture Room 102',
            'capacity' => 40,
            'room_type' => 'lecture',
        ]);

        $payload = [
            'tab' => 'timetable',
            'day_start' => '08:00',
            'day_end' => '17:00',
            'replace_existing' => '1',
        ];

        $this->post(route('programmes.timetable.auto-generate', $data['programme']), $payload);
        $firstDays = TimetableSlot::query()->orderBy('course_offering_id')->pluck('day_of_week')->all();

        $this->post(route('programmes.timetable.auto-generate', $data['programme']), $payload);
        $secondDays = TimetableSlot::query()->orderBy('course_offering_id')->pluck('day_of_week')->all();

        $this->assertNotSame($firstDays, $secondDays);
        foreach ($secondDays as $day) {
            $this->assertGreaterThanOrEqual(1, $day);
            $this->assertLessThanOrEqual(5, $day);
        }
    }
}
