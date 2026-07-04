<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_members', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('institution_id')->constrained()->nullOnDelete();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('programme_id')->constrained()->cascadeOnDelete();
            $table->string('student_number', 50);
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->unsignedTinyInteger('year_of_study')->default(1);
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->unique(['institution_id', 'student_number']);
            $table->unique(['institution_id', 'email']);
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('programme_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('title');
            $table->decimal('credit_hours', 4, 1)->default(3);
            $table->unsignedTinyInteger('year_level')->nullable();
            $table->unsignedTinyInteger('semester_number')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['programme_id', 'code']);
        });

        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 30);
            $table->string('name');
            $table->unsignedSmallInteger('capacity')->default(30);
            $table->string('room_type', 30)->default('lecture');
            $table->timestamps();

            $table->unique(['institution_id', 'code']);
        });

        Schema::create('course_offerings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_member_id')->nullable()->constrained()->nullOnDelete();
            $table->string('academic_year', 20);
            $table->unsignedTinyInteger('semester');
            $table->string('delivery_mode', 30)->default('face_to_face');
            $table->timestamps();

            $table->unique(['course_id', 'academic_year', 'semester'], 'course_offering_unique');
        });

        Schema::create('student_course_enrolments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_offering_id')->constrained()->cascadeOnDelete();
            $table->string('status', 30)->default('enrolled');
            $table->timestamps();

            $table->unique(['student_id', 'course_offering_id'], 'student_offering_unique');
        });

        Schema::create('timetable_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_offering_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('session_type', 30)->default('lecture');
            $table->string('venue_name')->nullable();
            $table->timestamps();
        });

        Schema::create('evaluation_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('academic_year', 20);
            $table->unsignedTinyInteger('semester');
            $table->string('title');
            $table->timestamp('opens_at');
            $table->timestamp('closes_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('evaluation_question_categories', function (Blueprint $table) {
            $table->id();
            $table->string('section', 20);
            $table->string('code', 50);
            $table->string('title');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique('code');
        });

        Schema::create('evaluation_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_question_category_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sequence_no');
            $table->text('question_text');
            $table->string('question_type', 20)->default('likert_5');
            $table->timestamps();
        });

        Schema::create('teaching_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_offering_id')->constrained()->cascadeOnDelete();
            $table->foreignId('evaluation_period_id')->constrained()->cascadeOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamps();

            $table->unique(['student_id', 'course_offering_id', 'evaluation_period_id'], 'teaching_eval_unique');
        });

        Schema::create('teaching_evaluation_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teaching_evaluation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('evaluation_question_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('response_text')->nullable();
            $table->timestamps();

            $table->unique(['teaching_evaluation_id', 'evaluation_question_id'], 'eval_response_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_evaluation_responses');
        Schema::dropIfExists('teaching_evaluations');
        Schema::dropIfExists('evaluation_questions');
        Schema::dropIfExists('evaluation_question_categories');
        Schema::dropIfExists('evaluation_periods');
        Schema::dropIfExists('timetable_slots');
        Schema::dropIfExists('student_course_enrolments');
        Schema::dropIfExists('course_offerings');
        Schema::dropIfExists('classrooms');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('students');

        Schema::table('staff_members', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
