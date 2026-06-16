<?php

namespace Tests\Feature;

use App\Enums\AccreditationRecommendation;
use App\Enums\AssessmentStatus;
use App\Enums\ComplianceStatus;
use App\Models\Assessment;
use App\Models\AssessmentCriterion;
use App\Models\AssessmentResponse;
use App\Models\AssessmentSection;
use App\Models\AssessmentTemplate;
use App\Models\Institution;
use App\Models\StandardVersion;
use App\Models\User;
use App\Services\ComplianceEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ComplianceEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_fully_compliant_when_average_at_least_three_and_no_mandatory_failures(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $institution = Institution::create(['name' => 'Test HEI', 'status' => 'active']);
        $version = StandardVersion::create(['name' => 'Test', 'code' => 'test', 'is_active' => true]);
        $template = AssessmentTemplate::create(['standard_version_id' => $version->id, 'type' => 'institutional', 'name' => 'Test']);
        $section = AssessmentSection::create(['assessment_template_id' => $template->id, 'code' => 'A1', 'title' => 'Area 1', 'divisor' => 2]);
        $c1 = AssessmentCriterion::create(['assessment_section_id' => $section->id, 'sequence_no' => 1, 'title' => 'C1', 'is_mandatory' => false]);
        $c2 = AssessmentCriterion::create(['assessment_section_id' => $section->id, 'sequence_no' => 2, 'title' => 'C2', 'is_mandatory' => true, 'minimum_score' => 3]);

        $assessment = Assessment::create([
            'institution_id' => $institution->id,
            'assessment_template_id' => $template->id,
            'assessment_type' => 'institutional',
            'title' => 'Test Assessment',
            'status' => AssessmentStatus::Draft,
        ]);

        AssessmentResponse::create(['assessment_id' => $assessment->id, 'assessment_criterion_id' => $c1->id, 'score' => 4]);
        AssessmentResponse::create(['assessment_id' => $assessment->id, 'assessment_criterion_id' => $c2->id, 'score' => 3]);

        $result = app(ComplianceEngine::class)->compute($assessment->fresh());

        $this->assertEquals(ComplianceStatus::FullyCompliant, $result->compliance_status);
        $this->assertEquals(AccreditationRecommendation::Ready, $result->accreditation_recommendation);
        $this->assertEquals(3.5, (float) $result->overall_average);
    }

    public function test_non_compliant_when_mandatory_criterion_fails(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $institution = Institution::create(['name' => 'Test HEI 2', 'status' => 'active']);
        $version = StandardVersion::create(['name' => 'Test', 'code' => 'test2', 'is_active' => true]);
        $template = AssessmentTemplate::create(['standard_version_id' => $version->id, 'type' => 'institutional', 'name' => 'Test']);
        $section = AssessmentSection::create(['assessment_template_id' => $template->id, 'code' => 'A1', 'title' => 'Area 1', 'divisor' => 1]);
        $c1 = AssessmentCriterion::create(['assessment_section_id' => $section->id, 'sequence_no' => 1, 'title' => 'Mandatory', 'is_mandatory' => true, 'minimum_score' => 3]);

        $assessment = Assessment::create([
            'institution_id' => $institution->id,
            'assessment_template_id' => $template->id,
            'assessment_type' => 'institutional',
            'title' => 'Fail Test',
            'status' => AssessmentStatus::Draft,
        ]);

        AssessmentResponse::create(['assessment_id' => $assessment->id, 'assessment_criterion_id' => $c1->id, 'score' => 2]);

        $result = app(ComplianceEngine::class)->compute($assessment->fresh());

        $this->assertEquals(ComplianceStatus::NonCompliant, $result->compliance_status);
        $this->assertNotEquals(AccreditationRecommendation::Ready, $result->accreditation_recommendation);
    }
}
