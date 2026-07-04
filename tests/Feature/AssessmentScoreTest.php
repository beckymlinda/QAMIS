<?php

namespace Tests\Feature;

use App\Enums\AssessmentStatus;
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
use Tests\TestCase;

class AssessmentScoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_saving_scores_with_unscored_items_updates_section_aggregate(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $institution = Institution::create(['name' => 'Test HEI', 'status' => 'active']);
        $version = StandardVersion::create(['name' => 'Test', 'code' => 'test-score', 'is_active' => true]);
        $template = AssessmentTemplate::create([
            'standard_version_id' => $version->id,
            'type' => 'institutional',
            'name' => 'Test',
        ]);
        $section = AssessmentSection::create([
            'assessment_template_id' => $template->id,
            'code' => 'A1',
            'title' => 'Area 1',
            'divisor' => 2,
        ]);
        $c1 = AssessmentCriterion::create([
            'assessment_section_id' => $section->id,
            'sequence_no' => 1,
            'title' => 'C1',
            'is_mandatory' => false,
        ]);
        $c2 = AssessmentCriterion::create([
            'assessment_section_id' => $section->id,
            'sequence_no' => 2,
            'title' => 'C2',
            'is_mandatory' => false,
        ]);

        $assessment = Assessment::create([
            'institution_id' => $institution->id,
            'assessment_template_id' => $template->id,
            'assessment_type' => 'institutional',
            'title' => 'Score Test',
            'status' => AssessmentStatus::Draft,
        ]);

        $r1 = AssessmentResponse::create([
            'assessment_id' => $assessment->id,
            'assessment_criterion_id' => $c1->id,
        ]);
        $r2 = AssessmentResponse::create([
            'assessment_id' => $assessment->id,
            'assessment_criterion_id' => $c2->id,
        ]);

        $user = User::factory()->create([
            'institution_id' => $institution->id,
        ]);
        $user->assignRole('qa_officer');

        $response = $this->actingAs($user)->post(route('assessments.score', $assessment), [
            'responses' => [
                ['id' => $r1->id, 'score' => '4', 'comments' => 'Strong evidence'],
                ['id' => $r2->id, 'score' => '', 'comments' => ''],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        app(ComplianceEngine::class)->compute($assessment->fresh());

        $summary = $assessment->fresh()->sectionSummaries()->first();

        $this->assertSame(4, $r1->fresh()->score);
        $this->assertNull($r2->fresh()->score);
        $this->assertNotNull($summary);
        $this->assertEquals(2.0, (float) $summary->aggregate_score);
        $this->assertEquals(4, $summary->total_score);
    }
}
