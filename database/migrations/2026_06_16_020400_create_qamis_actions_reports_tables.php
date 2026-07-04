<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assessment_response_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('severity')->default('medium');
            $table->timestamps();
        });

        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assessment_section_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assessment_criterion_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('prior_assessment_id')->nullable()->constrained('assessments')->nullOnDelete();
            $table->text('description');
            $table->string('source')->default('assessment');
            $table->string('status')->default('open');
            $table->text('progress_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('corrective_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recommendation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->date('deadline')->nullable();
            $table->string('status')->default('pending');
            $table->text('progress_notes')->nullable();
            $table->unsignedBigInteger('completion_evidence_id')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('completion_evidence_id', 'ca_completion_evidence_fk')
                ->references('id')->on('evidence_document_versions')->nullOnDelete();
        });

        Schema::create('report_templates', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('name');
            $table->string('version')->default('1.0');
            $table->json('section_schema')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('generated_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('report_template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reporting_year')->nullable();
            $table->string('status')->default('draft');
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('file_pdf_path')->nullable();
            $table->string('file_docx_path')->nullable();
            $table->json('snapshot_data')->nullable();
            $table->timestamps();
        });

        Schema::create('annual_report_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generated_report_id')->constrained()->cascadeOnDelete();
            $table->date('date_received')->nullable();
            $table->string('submitted_to_qama')->nullable();
            $table->date('reviewed_on')->nullable();
            $table->date('reviewed_by')->nullable();
            $table->text('nche_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('channel')->default('database');
            $table->boolean('email_enabled')->default(true);
            $table->boolean('in_app_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('compliance_dashboard_cache', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('scope_type')->default('institution');
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->decimal('overall_compliance_pct', 5, 2)->default(0);
            $table->json('by_standard')->nullable();
            $table->json('trend_data')->nullable();
            $table->unsignedInteger('outstanding_actions')->default(0);
            $table->decimal('evidence_completeness_pct', 5, 2)->default(0);
            $table->string('risk_level')->nullable();
            $table->timestamp('updated_at')->useCurrent();
        });

        Schema::create('external_evaluator_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->string('token')->unique();
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_evaluator_invitations');
        Schema::dropIfExists('compliance_dashboard_cache');
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('annual_report_submissions');
        Schema::dropIfExists('generated_reports');
        Schema::dropIfExists('report_templates');
        Schema::dropIfExists('corrective_actions');
        Schema::dropIfExists('recommendations');
        Schema::dropIfExists('findings');
    }
};
