<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\Programme;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgrammeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function institutionAdmin(): User
    {
        $this->seed(RolePermissionSeeder::class);

        $institution = Institution::create(['name' => 'Test Uni', 'acronym' => 'TU', 'status' => 'active']);
        $user = User::where('email', 'admin@demo-university.mw')->first();
        $user->update(['institution_id' => $institution->id]);

        return $user;
    }

    public function test_programme_create_page_renders_admission_fields(): void
    {
        $this->actingAs($this->institutionAdmin())
            ->get(route('programmes.create'))
            ->assertOk()
            ->assertSee('Academic & fees')
            ->assertSee('Tuition fee');
    }

    public function test_programme_can_be_registered(): void
    {
        $user = $this->institutionAdmin();

        $this->actingAs($user)
            ->post(route('programmes.store'), [
                'name' => 'Computer Science',
                'department' => 'Computing',
                'level' => 'bachelor',
                'delivery_modes' => ['fulltime'],
                'tuition_fee' => 500000,
                'application_fee' => 25000,
            ])
            ->assertRedirect(route('programmes.index'));

        $programme = Programme::first();
        $this->assertSame('Computer Science', $programme->name);
        $this->assertSame('500000.00', $programme->tuition_fee);
        $this->assertSame('25000.00', $programme->application_fee);
    }
}
