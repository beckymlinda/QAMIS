<?php

namespace Tests\Unit;

use App\Enums\AssessmentStatus;
use App\Models\Assessment;
use App\Models\AssessmentCriterion;
use App\Models\AssessmentResponse;
use App\Models\AssessmentSection;
use App\Models\AssessmentSectionSummary;
use App\Models\AssessmentTemplate;
use App\Models\Institution;
use App\Models\StandardVersion;
use App\Services\Reports\SarAssessmentFormatter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SarAssessmentFormatterTest extends TestCase
{
    use RefreshDatabase;

    public function test_strengths_and_improvements_use_reviewer_comments_by_score(): void
    {
        $institution = Institution::create(['name' => 'Test HEI', 'status' => 'active']);
        $version = StandardVersion::create(['name' => 'Test', 'code' => 'sar-formatter', 'is_active' => true]);
        $template = AssessmentTemplate::create([
            'standard_version_id' => $version->id,
            'type' => 'institutional',
            'name' => 'Test Template',
        ]);

        $section = AssessmentSection::create([
            'assessment_template_id' => $template->id,
            'code' => 'LIB',
            'title' => 'Library',
            'divisor' => 3,
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

        $uncommented = AssessmentCriterion::create([
            'assessment_section_id' => $section->id,
            'sequence_no' => 3,
            'title' => 'Scored without comment',
            'is_mandatory' => false,
        ]);

        $assessment = Assessment::create([
            'institution_id' => $institution->id,
            'assessment_template_id' => $template->id,
            'assessment_type' => 'institutional',
            'title' => 'SAR Test Assessment',
            'status' => AssessmentStatus::Draft,
        ]);

        AssessmentResponse::create([
            'assessment_id' => $assessment->id,
            'assessment_criterion_id' => $strong->id,
            'score' => 4,
            'comments' => 'E-library resources are readily accessible.',
            'strengths' => 'Auto rubric strength text should be ignored.',
        ]);

        AssessmentResponse::create([
            'assessment_id' => $assessment->id,
            'assessment_criterion_id' => $weak->id,
            'score' => 1,
            'comments' => 'Library staffing gaps need attention.',
            'areas_for_improvement' => 'Auto rubric improvement text should be ignored.',
        ]);

        AssessmentResponse::create([
            'assessment_id' => $assessment->id,
            'assessment_criterion_id' => $uncommented->id,
            'score' => 3,
            'strengths' => 'Should not appear without reviewer comment.',
        ]);

        AssessmentSectionSummary::create([
            'assessment_id' => $assessment->id,
            'assessment_section_id' => $section->id,
            'total_score' => 8,
            'aggregate_score' => 2.67,
            'divisor' => 3,
        ]);

        $rows = app(SarAssessmentFormatter::class)->buildStrengthsImprovementRows($assessment->fresh());

        $this->assertCount(1, $rows);
        $this->assertSame('Library', $rows[0]['area']);
        $this->assertSame(['E-library resources are readily accessible.'], $rows[0]['strengths']);
        $this->assertSame(['Library staffing gaps need attention.'], $rows[0]['improvements']);
    }

    public function test_reviewer_observations_are_used_when_comments_are_empty(): void
    {
        $institution = Institution::create(['name' => 'Test HEI', 'status' => 'active']);
        $version = StandardVersion::create(['name' => 'Test', 'code' => 'sar-observations', 'is_active' => true]);
        $template = AssessmentTemplate::create([
            'standard_version_id' => $version->id,
            'type' => 'programme',
            'name' => 'Programme Template',
        ]);

        $section = AssessmentSection::create([
            'assessment_template_id' => $template->id,
            'code' => 'PD',
            'title' => 'Programme design',
            'divisor' => 1,
            'sort_order' => 1,
        ]);

        $criterion = AssessmentCriterion::create([
            'assessment_section_id' => $section->id,
            'sequence_no' => 1,
            'title' => 'Curriculum alignment',
            'is_mandatory' => false,
        ]);

        $assessment = Assessment::create([
            'institution_id' => $institution->id,
            'assessment_template_id' => $template->id,
            'assessment_type' => 'programme',
            'title' => 'Programme SAR Test',
            'status' => AssessmentStatus::Draft,
        ]);

        AssessmentResponse::create([
            'assessment_id' => $assessment->id,
            'assessment_criterion_id' => $criterion->id,
            'score' => 2,
            'reviewer_observations' => 'Learning outcomes are not aligned with course topics.',
        ]);

        AssessmentSectionSummary::create([
            'assessment_id' => $assessment->id,
            'assessment_section_id' => $section->id,
            'total_score' => 2,
            'aggregate_score' => 2.00,
            'divisor' => 1,
        ]);

        $rows = app(SarAssessmentFormatter::class)->buildStrengthsImprovementRows($assessment->fresh());

        $this->assertCount(1, $rows);
        $this->assertSame([], $rows[0]['strengths']);
        $this->assertSame(['Learning outcomes are not aligned with course topics.'], $rows[0]['improvements']);
    }
}
