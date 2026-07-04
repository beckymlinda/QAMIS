<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_course_enrolment_id')->constrained()->cascadeOnDelete();
            $table->decimal('coursework_percentage', 5, 2)->nullable();
            $table->decimal('exam_percentage', 5, 2)->nullable();
            $table->decimal('final_percentage', 5, 2)->nullable();
            $table->string('letter_grade', 5)->nullable();
            $table->decimal('grade_points', 4, 2)->nullable();
            $table->string('quality_label')->nullable();
            $table->string('academic_decision')->nullable();
            $table->boolean('is_published')->default(false);
            $table->foreignId('graded_by_staff_member_id')->nullable()->constrained('staff_members')->nullOnDelete();
            $table->timestamp('graded_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique('student_course_enrolment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_results');
    }
};
