<?php

namespace Tests\Feature\Auth;

use App\Models\Institution;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $response = $this->post('/register', [
            'institution_name' => 'New College of Malawi',
            'institution_acronym' => 'NCM',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('institutions.report-data.index', auth()->user()->institution_id));

        $this->assertTrue(auth()->user()->hasRole('institution_admin'));
        $this->assertNotNull(auth()->user()->institution_id);
        $this->assertDatabaseHas('institutions', ['name' => 'New College of Malawi']);
    }
}
