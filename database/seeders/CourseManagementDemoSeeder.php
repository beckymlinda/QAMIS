<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseOffering;
use App\Models\CourseResult;
use App\Models\EvaluationPeriod;
use App\Models\Institution;
use App\Models\Programme;
use App\Models\StaffMember;
use App\Models\Student;
use App\Models\StudentCourseEnrolment;
use App\Models\TimetableSlot;
use App\Models\User;
use App\Support\GpaGrading;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CourseManagementDemoSeeder extends Seeder
{
    public function run(): void
    {
        $institution = Institution::where('acronym', 'DUM')->first();
        if (! $institution) {
            return;
        }

        $programme = Programme::firstOrCreate(
            [
                'institution_id' => $institution->id,
                'name' => 'Bachelor of Business Administration',
            ],
            [
                'level' => 'undergraduate',
                'delivery_modes' => ['face_to_face'],
                'nche_accreditation_status' => 'accredited',
            ]
        );

        $lecturerUser = User::firstOrCreate(
            ['email' => 'lecturer@demo-university.mw', 'institution_id' => $institution->id],
            [
                'name' => 'Dr. Jane Phiri',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $lecturerUser->assignRole('lecturer');

        $lecturer = StaffMember::firstOrCreate(
            [
                'institution_id' => $institution->id,
                'programme_id' => $programme->id,
                'name' => 'Dr. Jane Phiri',
            ],
            [
                'type' => 'academic',
                'designation' => 'Senior Lecturer',
                'qualification' => 'PhD Business Administration',
                'user_id' => $lecturerUser->id,
            ]
        );
        $lecturer->update(['user_id' => $lecturerUser->id]);

        $studentUser = User::firstOrCreate(
            ['email' => 'student@demo-university.mw', 'institution_id' => $institution->id],
            [
                'name' => 'Chisomo Banda',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $studentUser->assignRole('student');

        $student = Student::firstOrCreate(
            ['institution_id' => $institution->id, 'student_number' => 'DUM2026001'],
            [
                'user_id' => $studentUser->id,
                'programme_id' => $programme->id,
                'first_name' => 'Chisomo',
                'last_name' => 'Banda',
                'email' => 'student@demo-university.mw',
                'year_of_study' => 1,
                'status' => 'active',
            ]
        );
        $student->update(['user_id' => $studentUser->id]);

        $courses = [
            ['code' => 'BUSI-1101', 'title' => 'Introduction to Business', 'credit_hours' => 4],
            ['code' => 'MGMT-1102', 'title' => 'Principles of Management', 'credit_hours' => 3.5],
            ['code' => 'COMM-1003', 'title' => 'Business Communication', 'credit_hours' => 4],
        ];

        $year = date('Y').'/'.((int) date('Y') + 1);
        $classroom = Classroom::firstOrCreate(
            ['institution_id' => $institution->id, 'code' => 'LR-101'],
            ['name' => 'Lecture Room 101', 'capacity' => 60, 'room_type' => 'lecture']
        );

        foreach ($courses as $index => $courseData) {
            $course = Course::firstOrCreate(
                ['programme_id' => $programme->id, 'code' => $courseData['code']],
                [
                    'institution_id' => $institution->id,
                    'title' => $courseData['title'],
                    'credit_hours' => $courseData['credit_hours'],
                    'year_level' => 1,
                    'semester_number' => 1,
                ]
            );

            $offering = CourseOffering::firstOrCreate(
                [
                    'course_id' => $course->id,
                    'academic_year' => $year,
                    'semester' => 1,
                ],
                [
                    'institution_id' => $institution->id,
                    'staff_member_id' => $lecturer->id,
                    'delivery_mode' => 'face_to_face',
                ]
            );

            StudentCourseEnrolment::firstOrCreate([
                'student_id' => $student->id,
                'course_offering_id' => $offering->id,
            ]);

            $enrolment = StudentCourseEnrolment::where('student_id', $student->id)
                ->where('course_offering_id', $offering->id)
                ->first();

            $demoMarks = [
                'BUSI-1101' => ['coursework' => 78, 'exam' => 82],
                'MGMT-1102' => ['coursework' => 65, 'exam' => 70],
                'COMM-1003' => ['coursework' => 88, 'exam' => 90],
            ];
            $marks = $demoMarks[$courseData['code']] ?? ['coursework' => 70, 'exam' => 72];
            $final = GpaGrading::computeFinal($marks['coursework'], $marks['exam']);
            $band = GpaGrading::fromPercentage($final);

            CourseResult::updateOrCreate(
                ['student_course_enrolment_id' => $enrolment->id],
                [
                    'coursework_percentage' => $marks['coursework'],
                    'exam_percentage' => $marks['exam'],
                    'final_percentage' => $final,
                    'letter_grade' => $band['letter'],
                    'grade_points' => $band['points'],
                    'quality_label' => $band['quality'],
                    'academic_decision' => $band['decision'],
                    'is_published' => true,
                    'published_at' => now(),
                    'graded_by_staff_member_id' => $lecturer->id,
                    'graded_at' => now(),
                ]
            );

            TimetableSlot::firstOrCreate(
                [
                    'course_offering_id' => $offering->id,
                    'day_of_week' => ($index % 5) + 1,
                    'start_time' => '08:00:00',
                ],
                [
                    'end_time' => '10:00:00',
                    'classroom_id' => $classroom->id,
                    'session_type' => 'lecture',
                ]
            );
        }

        EvaluationPeriod::firstOrCreate(
            [
                'institution_id' => $institution->id,
                'academic_year' => $year,
                'semester' => 1,
            ],
            [
                'title' => 'Semester 1 Teaching Evaluation',
                'opens_at' => now()->subDays(1),
                'closes_at' => now()->addMonths(1),
                'is_active' => true,
            ]
        );
    }
}
