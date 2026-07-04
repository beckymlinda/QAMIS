<?php

namespace Tests\Feature;

use App\Enums\AssessmentStatus;
use App\Models\Assessment;
use App\Models\AssessmentCriterion;
use App\Models\AssessmentCriterionRubricLevel;
use App\Models\AssessmentResponse;
use App\Models\AssessmentSection;
use App\Models\AssessmentTemplate;
use App\Models\Institution;
use App\Models\StandardVersion;
use App\Services\AssessmentStrengthsAnalysis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentStrengthsAnalysisTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_groups_strengths_and_improvements_by_score(): void
    {
        $institution = Institution::create(['name' => 'Test HEI', 'status' => 'active']);
        $version = StandardVersion::create(['name' => 'Test', 'code' => 'test-analysis', 'is_active' => true]);
        $template = AssessmentTemplate::create([
            'standard_version_id' => $version->id,
            'type' => 'institutional',
            'name' => 'Test Template',
        ]);

        $section = AssessmentSection::create([
            'assessment_template_id' => $template->id,
            'code' => 'TEST',
            'title' => 'Test Section',
            'divisor' => 2,
            'sort_order' => 1,
        ]);

        $strong = AssessmentCriterion::create([
            'assessment_section_id' => $section->id,
            'sequence_no' => 1,
            'title' => 'Strong criterion',
            'is_mandatory' => false,
        ]);

        $weak = AssessmentCriterion::create([
            'assessment_section_id' => $section->id,
            'sequence_no' => 2,
            'title' => 'Weak criterion',
            'is_mandatory' => false,
        ]);

        $assessment = Assessment::create([
            'institution_id' => $institution->id,
            'assessment_template_id' => $template->id,
            'assessment_type' => 'institutional',
            'title' => 'Test Assessment',
            'status' => AssessmentStatus::Draft,
        ]);

        AssessmentResponse::create([
            'assessment_id' => $assessment->id,
            'assessment_criterion_id' => $strong->id,
            'score' => 4,
            'comments' => 'Excellent evidence provided.',
        ]);

        AssessmentResponse::create([
            'assessment_id' => $assessment->id,
            'assessment_criterion_id' => $weak->id,
            'score' => 1,
            'comments' => 'Major gaps observed.',
        ]);

        AssessmentCriterionRubricLevel::create([
            'assessment_criterion_id' => $strong->id,
            'score' => 4,
            'level_label' => 'Excellent',
            'descriptor' => 'Fully meets the standard.',
        ]);

        $analysis = app(AssessmentStrengthsAnalysis::class)->analyze($assessment->fresh());

        $this->assertCount(1, $analysis['strengths']);
        $this->assertCount(1, $analysis['areas_for_improvement']);
        $this->assertSame('Strong criterion', $analysis['strengths']->first()->criterion->title);
        $this->assertSame('Weak criterion', $analysis['areas_for_improvement']->first()->criterion->title);
    }

    public function test_gap_guidance_explains_failure_and_pass_requirements(): void
    {
        $institution = Institution::create(['name' => 'Test HEI', 'status' => 'active']);
        $version = StandardVersion::create(['name' => 'Test', 'code' => 'test-guidance', 'is_active' => true]);
        $template = AssessmentTemplate::create([
            'standard_version_id' => $version->id,
            'type' => 'institutional',
            'name' => 'Test Template',
        ]);

        $section = AssessmentSection::create([
            'assessment_template_id' => $template->id,
            'code' => 'TEST',
            'title' => 'Test Section',
            'divisor' => 1,
            'sort_order' => 1,
        ]);

        $criterion = AssessmentCriterion::create([
            'assessment_section_id' => $section->id,
            'sequence_no' => 1,
            'title' => 'Mandatory policy',
            'is_mandatory' => true,
            'minimum_score' => 3,
        ]);

        $assessment = Assessment::create([
            'institution_id' => $institution->id,
            'assessment_template_id' => $template->id,
            'assessment_type' => 'institutional',
            'title' => 'Test Assessment',
            'status' => AssessmentStatus::Draft,
        ]);

        $response = AssessmentResponse::create([
            'assessment_id' => $assessment->id,
            'assessment_criterion_id' => $criterion->id,
            'score' => 1,
            'comments' => 'Policy incomplete.',
        ]);

        AssessmentCriterionRubricLevel::create([
            'assessment_criterion_id' => $criterion->id,
            'score' => 1,
            'level_label' => 'Insufficient',
            'descriptor' => 'Partially meets the standard with significant deficiencies.',
        ]);

        AssessmentCriterionRubricLevel::create([
            'assessment_criterion_id' => $criterion->id,
            'score' => 3,
            'level_label' => 'Good',
            'descriptor' => 'Policy is complete and consistently applied.',
        ]);

        $guidance = app(AssessmentStrengthsAnalysis::class)->gapGuidance($response->fresh(['criterion.rubricLevels']));

        $this->assertStringContainsString('Scored 1/4', $guidance['why_failed']);
        $this->assertStringContainsString('mandatory', strtolower($guidance['why_failed']));
        $this->assertStringContainsString('To pass', $guidance['action_required']);
        $this->assertStringContainsString('Policy is complete', $guidance['action_required']);
    }
}
