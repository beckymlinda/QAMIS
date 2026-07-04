<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\AssessmentTemplate;
use App\Models\Institution;
use App\Models\Programme;
use App\Models\StandardVersion;
use App\Models\User;
use App\Support\InstitutionContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_institution_user_only_sees_own_programmes(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $instA = Institution::create(['name' => 'University A', 'status' => 'active']);
        $instB = Institution::create(['name' => 'University B', 'status' => 'active']);

        Programme::create(['institution_id' => $instA->id, 'name' => 'Programme A', 'level' => 'bachelor']);
        Programme::create(['institution_id' => $instB->id, 'name' => 'Programme B', 'level' => 'bachelor']);

        $userA = User::where('email', 'admin@demo-university.mw')->first();
        $userA->update(['institution_id' => $instA->id]);

        InstitutionContext::set($instA->id);

        $this->assertEquals(1, Programme::count());
        $this->assertEquals('Programme A', Programme::first()->name);
    }

    public function test_institution_user_assessments_index_only_shows_own_records(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $instA = Institution::create(['name' => 'University A', 'status' => 'active']);
        $instB = Institution::create(['name' => 'University B', 'status' => 'active']);

        $user = User::where('email', 'admin@demo-university.mw')->first();
        $user->update(['institution_id' => $instA->id]);

        $version = StandardVersion::create(['name' => 'Test', 'code' => 'tenant-test', 'is_active' => true]);
        $template = AssessmentTemplate::create([
            'standard_version_id' => $version->id,
            'type' => 'institutional',
            'name' => 'Tenant Test Template',
        ]);

        Assessment::create([
            'institution_id' => $instA->id,
            'assessment_template_id' => $template->id,
            'assessment_type' => 'institutional',
            'title' => 'Assessment A',
            'status' => 'draft',
        ]);
        Assessment::create([
            'institution_id' => $instB->id,
            'assessment_template_id' => $template->id,
            'assessment_type' => 'institutional',
            'title' => 'Assessment B',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($user)->get(route('assessments.index'));

        $response->assertOk();
        $response->assertSee('Assessment A');
        $response->assertDontSee('Assessment B');
    }
}
