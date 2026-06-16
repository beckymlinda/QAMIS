<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('standard_versions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('standard_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('standard_version_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('standard_areas')->nullOnDelete();
            $table->string('code');
            $table->string('title');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('standard_clauses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('standard_area_id')->constrained()->cascadeOnDelete();
            $table->string('reference_code');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('assessment_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('standard_version_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('name');
            $table->string('version')->default('1.0');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('assessment_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('standard_area_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code');
            $table->string('title');
            $table->string('minimum_standard_ref')->nullable();
            $table->unsignedInteger('divisor')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('assessment_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_criterion_id')->nullable()->constrained('assessment_criteria')->nullOnDelete();
            $table->foreignId('standard_clause_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('sequence_no');
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_mandatory')->default(false);
            $table->unsignedTinyInteger('minimum_score')->default(3);
            $table->decimal('weight', 8, 2)->default(1);
            $table->text('source_text')->nullable();
            $table->timestamps();
        });

        Schema::create('scoring_rubrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('standard_version_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('score');
            $table->string('label');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['standard_version_id', 'score']);
        });

        Schema::create('compliance_thresholds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('standard_version_id')->constrained()->cascadeOnDelete();
            $table->string('assessment_type');
            $table->decimal('fully_compliant_min', 4, 2)->default(3.00);
            $table->decimal('partially_compliant_min', 4, 2)->default(2.00);
            $table->decimal('partially_compliant_max', 4, 2)->default(2.99);
            $table->decimal('non_compliant_max', 4, 2)->default(1.99);
            $table->unsignedTinyInteger('mandatory_minimum_score')->default(3);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_thresholds');
        Schema::dropIfExists('scoring_rubrics');
        Schema::dropIfExists('assessment_criteria');
        Schema::dropIfExists('assessment_sections');
        Schema::dropIfExists('assessment_templates');
        Schema::dropIfExists('standard_clauses');
        Schema::dropIfExists('standard_areas');
        Schema::dropIfExists('standard_versions');
    }
};
