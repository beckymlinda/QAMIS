<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_course_outlines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_offering_id')->constrained()->cascadeOnDelete();
            $table->text('learning_outcomes')->nullable();
            $table->text('assessment_plan')->nullable();
            $table->text('weekly_schedule')->nullable();
            $table->timestamps();

            $table->unique('course_offering_id');
        });

        Schema::create('lms_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_offering_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamp('visible_from')->nullable();
            $table->timestamp('visible_until')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        Schema::create('lms_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_module_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('type', 32)->default('document');
            $table->string('file_path')->nullable();
            $table->string('external_url')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('allow_download')->default(true);
            $table->timestamps();
        });

        Schema::create('lms_announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_offering_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('lms_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_offering_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('instructions')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->unsignedSmallInteger('max_score')->default(100);
            $table->boolean('allow_late')->default(false);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        Schema::create('lms_assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->text('body')->nullable();
            $table->string('file_path')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['lms_assignment_id', 'student_id']);
        });

        Schema::create('lms_discussions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_offering_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();
        });

        Schema::create('lms_discussion_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_discussion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('lms_discussion_posts')->nullOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('lms_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('link')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('lms_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_offering_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_activity_logs');
        Schema::dropIfExists('lms_notifications');
        Schema::dropIfExists('lms_discussion_posts');
        Schema::dropIfExists('lms_discussions');
        Schema::dropIfExists('lms_assignment_submissions');
        Schema::dropIfExists('lms_assignments');
        Schema::dropIfExists('lms_announcements');
        Schema::dropIfExists('lms_materials');
        Schema::dropIfExists('lms_modules');
        Schema::dropIfExists('lms_course_outlines');
    }
};
