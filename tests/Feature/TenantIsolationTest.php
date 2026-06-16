<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\Programme;
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
}
