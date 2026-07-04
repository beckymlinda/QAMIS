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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentSubmitGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_submit_is_blocked_when_no_scores_have_been_saved(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $institution = Institution::create(['name' => 'Test HEI', 'status' => 'active']);
        $version = StandardVersion::create(['name' => 'Test', 'code' => 'test-submit', 'is_active' => true]);
        $template = AssessmentTemplate::create([
            'standard_version_id' => $version->id,
            'type' => 'institutional',
            'name' => 'Test',
        ]);
        $section = AssessmentSection::create([
            'assessment_template_id' => $template->id,
            'code' => 'A1',
            'title' => 'Area 1',
            'divisor' => 1,
        ]);
        $criterion = AssessmentCriterion::create([
            'assessment_section_id' => $section->id,
            'sequence_no' => 1,
            'title' => 'C1',
            'is_mandatory' => false,
        ]);

        $assessment = Assessment::create([
            'institution_id' => $institution->id,
            'assessment_template_id' => $template->id,
            'assessment_type' => 'institutional',
            'title' => 'Submit Test',
            'status' => AssessmentStatus::Draft,
        ]);

        AssessmentResponse::create([
            'assessment_id' => $assessment->id,
            'assessment_criterion_id' => $criterion->id,
        ]);

        $user = User::factory()->create(['institution_id' => $institution->id]);
        $user->assignRole('qa_officer');

        $this->actingAs($user)
            ->post(route('assessments.transition', $assessment), ['status' => 'submitted'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(AssessmentStatus::Draft, $assessment->fresh()->status);
    }
}
