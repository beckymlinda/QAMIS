<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function institutionAdmin(): User
    {
        $this->seed(RolePermissionSeeder::class);

        $institution = Institution::create(['name' => 'User Uni', 'acronym' => 'UU', 'status' => 'active']);
        $admin = User::where('email', 'admin@demo-university.mw')->first();
        $admin->update(['institution_id' => $institution->id]);

        return $admin;
    }

    public function test_institution_admin_can_view_user_management(): void
    {
        $this->actingAs($this->institutionAdmin())
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('User management');
    }

    public function test_institution_admin_can_create_staff_user(): void
    {
        $admin = $this->institutionAdmin();

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'QA Staff',
                'email' => 'qa.staff@user-uni.mw',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role' => 'qa_officer',
                'is_active' => '1',
            ])
            ->assertRedirect(route('users.index'));

        $user = User::where('email', 'qa.staff@user-uni.mw')->first();
        $this->assertNotNull($user);
        $this->assertSame($admin->institution_id, $user->institution_id);
        $this->assertTrue($user->hasRole('qa_officer'));
    }

    public function test_institution_admin_can_create_custom_role(): void
    {
        $admin = $this->institutionAdmin();

        $this->actingAs($admin)
            ->post(route('roles.store'), [
                'name' => 'finance_officer',
                'permissions' => ['report.view', 'dashboard.view'],
            ])
            ->assertRedirect(route('roles.index'));

        $role = Role::where('name', 'finance_officer')->first();
        $this->assertNotNull($role);
        $this->assertTrue($role->hasPermissionTo('report.view'));
    }

    public function test_institution_admin_cannot_assign_system_admin_role(): void
    {
        $admin = $this->institutionAdmin();

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Bad Admin',
                'email' => 'bad@user-uni.mw',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role' => 'system_admin',
            ])
            ->assertSessionHasErrors('role');
    }
}
