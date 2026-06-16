<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evidence_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('evidence_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('evidence_category_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('current_version_id')->nullable();
            $table->timestamps();
        });

        Schema::create('evidence_document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evidence_document_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version_no');
            $table->string('file_path');
            $table->string('original_filename');
            $table->string('mime_type')->nullable();
            $table->string('checksum')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamps();

            $table->unique(['evidence_document_id', 'version_no']);
        });

        Schema::create('evidence_standard_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evidence_document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('standard_clause_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['evidence_document_id', 'standard_clause_id'], 'evidence_clause_unique');
        });

        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('programme_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assessment_template_id')->constrained()->cascadeOnDelete();
            $table->string('assessment_type');
            $table->string('title');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->string('status')->default('draft');
            $table->string('assessor_names')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();

            $table->index(['institution_id', 'status']);
            $table->index(['institution_id', 'assessment_type']);
        });

        Schema::create('assessment_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_criterion_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('score')->nullable();
            $table->boolean('is_available')->nullable();
            $table->text('comments')->nullable();
            $table->text('reviewer_observations')->nullable();
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('recommendations')->nullable();
            $table->foreignId('scored_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('scored_at')->nullable();
            $table->timestamps();

            $table->unique(['assessment_id', 'assessment_criterion_id']);
        });

        Schema::create('assessment_response_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_response_id')->constrained()->cascadeOnDelete();
            $table->foreignId('evidence_document_version_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('assessment_section_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_section_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('total_score')->default(0);
            $table->decimal('aggregate_score', 5, 2)->default(0);
            $table->unsignedInteger('divisor')->default(1);
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('recommendations')->nullable();
            $table->timestamps();

            $table->unique(['assessment_id', 'assessment_section_id'], 'assessment_section_summary_unique');
        });

        Schema::create('compliance_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->decimal('overall_average', 5, 2)->default(0);
            $table->string('compliance_status');
            $table->string('accreditation_recommendation');
            $table->json('mandatory_failures')->nullable();
            $table->string('risk_level')->nullable();
            $table->boolean('missing_mandatory_evidence')->default(false);
            $table->timestamp('computed_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('assessment_workflow_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('assessment_team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('role')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_team_members');
        Schema::dropIfExists('assessment_workflow_history');
        Schema::dropIfExists('compliance_results');
        Schema::dropIfExists('assessment_section_summaries');
        Schema::dropIfExists('assessment_response_evidence');
        Schema::dropIfExists('assessment_responses');
        Schema::dropIfExists('assessments');
        Schema::dropIfExists('evidence_standard_links');
        Schema::dropIfExists('evidence_document_versions');
        Schema::dropIfExists('evidence_documents');
        Schema::dropIfExists('evidence_categories');
    }
};
