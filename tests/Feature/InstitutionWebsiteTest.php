<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\InstitutionWebsiteSetting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InstitutionWebsiteTest extends TestCase
{
    use RefreshDatabase;

    protected function adminWithInstitution(): array
    {
        $this->seed(RolePermissionSeeder::class);

        $institution = Institution::create(['name' => 'Test University', 'acronym' => 'TU', 'status' => 'active']);

        $user = User::create([
            'institution_id' => $institution->id,
            'name' => 'Admin User',
            'email' => 'admin@test.mw',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $user->assignRole('institution_admin');

        return [$institution, $user];
    }

    public function test_admin_can_access_website_settings(): void
    {
        [$institution, $user] = $this->adminWithInstitution();

        $this->actingAs($user)
            ->get(route('settings.website.edit', $institution))
            ->assertOk()
            ->assertSee('Website contents');
    }

    public function test_admin_can_save_and_publish_website(): void
    {
        [$institution, $user] = $this->adminWithInstitution();

        $this->actingAs($user)
            ->put(route('settings.website.update', $institution), [
                'slug' => 'test-university',
                'school_name' => 'Test University',
                'tagline' => 'Excellence in learning',
                'hero_description' => 'Welcome to our campus.',
                'about_content' => 'We are a leading institution.',
                'primary_color' => '#112233',
                'secondary_color' => '#aabbcc',
            ])
            ->assertRedirect(route('settings.website.edit', $institution));

        $this->actingAs($user)
            ->post(route('settings.website.toggle-publish', $institution))
            ->assertRedirect(route('settings.website.edit', $institution));

        $settings = InstitutionWebsiteSetting::where('institution_id', $institution->id)->first();
        $this->assertTrue($settings->is_published);
        $this->assertSame('Test University', $settings->school_name);

        $this->get(route('school.home', 'test-university'))
            ->assertOk()
            ->assertSee('Test University')
            ->assertSee('Excellence in learning');
    }

    public function test_unpublished_website_is_not_public(): void
    {
        [$institution, $user] = $this->adminWithInstitution();

        InstitutionWebsiteSetting::forInstitution($institution)->update([
            'slug' => 'hidden-school',
            'is_published' => false,
        ]);

        $this->get(route('school.home', 'hidden-school'))->assertNotFound();
    }

    public function test_default_welcome_page_unchanged(): void
    {
        $this->get(route('welcome'))
            ->assertOk()
            ->assertSee('Welcome to HEQAMIS Self-Assessment');
    }

    public function test_admin_can_save_team_members(): void
    {
        [$institution, $user] = $this->adminWithInstitution();

        $this->actingAs($user)
            ->put(route('settings.website.update', $institution), [
                'slug' => 'team-uni',
                'school_name' => 'Team Uni',
                'about_content' => 'About our school.',
                'primary_color' => '#112233',
                'secondary_color' => '#aabbcc',
                'team_members' => [
                    ['name' => 'Jane Doe', 'role' => 'Principal'],
                ],
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('settings.website.toggle-publish', $institution));

        $this->get(route('school.about', 'team-uni'))
            ->assertOk()
            ->assertSee('Jane Doe')
            ->assertSee('Principal');
    }

    public function test_student_portal_page_is_public(): void
    {
        [$institution, $user] = $this->adminWithInstitution();

        InstitutionWebsiteSetting::forInstitution($institution)->update([
            'slug' => 'portal-uni',
            'is_published' => true,
        ]);

        $this->get(route('school.portal', 'portal-uni'))
            ->assertOk()
            ->assertSee('Student portal')
            ->assertSee('Create applicant account')
            ->assertSee('Student login');
    }

    public function test_enrolled_student_can_login_via_school_portal(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $institution = Institution::create(['name' => 'Portal Uni', 'acronym' => 'PU', 'status' => 'active']);
        InstitutionWebsiteSetting::forInstitution($institution)->update([
            'slug' => 'portal-uni',
            'is_published' => true,
        ]);

        $studentUser = User::create([
            'institution_id' => $institution->id,
            'name' => 'Enrolled Student',
            'email' => 'student@portal.mw',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $studentUser->assignRole('student');

        $this->post('/school/portal-uni/portal/student/login', [
            'email' => 'student@portal.mw',
            'password' => 'password',
        ])->assertRedirect(route('student.dashboard'));

        $this->assertAuthenticatedAs($studentUser);
    }
}
