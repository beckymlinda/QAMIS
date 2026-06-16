<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institution_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->text('vision')->nullable();
            $table->text('mission')->nullable();
            $table->text('core_values')->nullable();
            $table->text('strategic_plan_summary')->nullable();
            $table->text('background_narrative')->nullable();
            $table->json('swot_analysis')->nullable();
            $table->timestamps();
        });

        Schema::create('institution_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('postal_address')->nullable();
            $table->string('fax')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('telephone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('location')->nullable();
            $table->timestamps();
        });

        Schema::create('org_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('org_units')->nullOnDelete();
            $table->string('type');
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['institution_id', 'type']);
        });

        Schema::create('programmes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('org_unit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('level');
            $table->json('delivery_modes')->nullable();
            $table->string('nche_accreditation_status')->default('pending');
            $table->string('professional_body')->nullable();
            $table->date('curriculum_developed_at')->nullable();
            $table->date('curriculum_reviewed_at')->nullable();
            $table->date('accredited_at')->nullable();
            $table->timestamps();

            $table->index(['institution_id', 'org_unit_id']);
        });

        Schema::create('governance_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('body_type');
            $table->string('name');
            $table->string('gender')->nullable();
            $table->string('qualification')->nullable();
            $table->string('awarding_institution')->nullable();
            $table->string('designation')->nullable();
            $table->string('specialization')->nullable();
            $table->unsignedSmallInteger('experience_years')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('staff_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('org_unit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('programme_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->string('name');
            $table->string('gender')->nullable();
            $table->string('qualification')->nullable();
            $table->string('awarding_institution')->nullable();
            $table->unsignedSmallInteger('qualification_year')->nullable();
            $table->string('rank')->nullable();
            $table->string('designation')->nullable();
            $table->string('employment_type')->nullable();
            $table->string('courses_taught')->nullable();
            $table->unsignedSmallInteger('experience_years')->nullable();
            $table->timestamps();
        });

        Schema::create('student_enrolments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('programme_id')->nullable()->constrained()->nullOnDelete();
            $table->string('qualification_type');
            $table->string('delivery_mode')->nullable();
            $table->unsignedInteger('male_count')->default(0);
            $table->unsignedInteger('female_count')->default(0);
            $table->string('citizenship')->nullable();
            $table->boolean('has_disability')->default(false);
            $table->string('reporting_year')->nullable();
            $table->timestamps();
        });

        Schema::create('student_fee_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('qualification_type');
            $table->string('delivery_mode')->nullable();
            $table->decimal('local_fee', 12, 2)->nullable();
            $table->decimal('international_fee', 12, 2)->nullable();
            $table->string('reporting_year')->nullable();
            $table->timestamps();
        });

        Schema::create('student_age_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('age_range');
            $table->decimal('male_percent', 5, 2)->default(0);
            $table->decimal('female_percent', 5, 2)->default(0);
            $table->string('reporting_year')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_age_distributions');
        Schema::dropIfExists('student_fee_schedules');
        Schema::dropIfExists('student_enrolments');
        Schema::dropIfExists('staff_members');
        Schema::dropIfExists('governance_members');
        Schema::dropIfExists('programmes');
        Schema::dropIfExists('org_units');
        Schema::dropIfExists('institution_contacts');
        Schema::dropIfExists('institution_profiles');
    }
};
