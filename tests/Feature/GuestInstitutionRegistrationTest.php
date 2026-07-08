<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestInstitutionRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_register_and_sees_limited_sidebar(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $this->get(route('register.guest'))->assertOk()->assertSee('Register as guest institution');

        $this->post(route('register.guest'), [
            'institution_name' => 'Demo College',
            'institution_acronym' => 'DC',
            'name' => 'Demo Admin',
            'email' => 'demo@guest.mw',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertRedirect(route('dashboard'));

        $user = User::where('email', 'demo@guest.mw')->first();
        $this->assertTrue($user->hasRole('guest_institution'));

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Assessment Tools')
            ->assertSee('Programmes')
            ->assertDontSee('User Management')
            ->assertDontSee('Student Management');
    }

    public function test_login_page_shows_guest_register_not_full_register(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Register as guest institution')
            ->assertDontSee('Need an institution account?');
    }
}
