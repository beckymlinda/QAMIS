<?php

namespace Database\Seeders;

use App\Models\Institution;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'institution.manage', 'programme.manage', 'assessment.create', 'assessment.score',
            'assessment.review', 'assessment.approve', 'evidence.upload', 'report.generate',
            'report.view', 'standards.manage', 'corrective_action.manage', 'dashboard.view',
            'user.manage', 'institution.create', 'student.portal', 'evaluation.view_reports',
            'grade.manage', 'lms.manage', 'lms.view', 'application.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $roles = [
            'system_admin' => Permission::all()->pluck('name')->toArray(),
            'nche_admin' => [
                'institution.create', 'institution.manage', 'standards.manage',
                'report.view', 'dashboard.view', 'assessment.review', 'assessment.approve',
            ],
            'institution_admin' => [
                'institution.manage', 'programme.manage', 'user.manage', 'dashboard.view',
                'report.generate', 'report.view', 'assessment.create', 'assessment.score', 'evidence.upload',
                'application.manage',
            ],
            'qa_officer' => [
                'assessment.create', 'assessment.score', 'assessment.review', 'report.generate',
                'report.view', 'evidence.upload', 'corrective_action.manage', 'dashboard.view',
            ],
            'data_entry_officer' => ['assessment.create', 'evidence.upload', 'programme.manage'],
            'department_head' => ['assessment.create', 'assessment.score', 'programme.manage', 'report.view'],
            'reviewer_assessor' => ['assessment.score', 'assessment.review', 'evidence.upload', 'report.view'],
            'executive_management' => ['dashboard.view', 'report.view'],
            'council_board' => ['report.view', 'dashboard.view'],
            'external_evaluator' => ['report.view', 'assessment.review'],
            'student' => ['student.portal', 'lms.view'],
            'applicant' => [],
            'lecturer' => ['dashboard.view', 'evaluation.view_reports', 'grade.manage', 'lms.manage'],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }

        $systemAdmin = User::firstOrCreate(
            ['email' => 'admin@heqamis.mw'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $systemAdmin->assignRole('system_admin');

        $ncheAdmin = User::firstOrCreate(
            ['email' => 'nche@heqamis.mw'],
            [
                'name' => 'Standards Administrator',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $ncheAdmin->assignRole('nche_admin');

        $demoInstitution = Institution::firstOrCreate(
            ['name' => 'Demo University of Malawi'],
            [
                'acronym' => 'DUM',
                'establishment_year' => 2000,
                'status' => 'active',
                'programme_levels' => ['undergraduate', 'masters', 'doctorate'],
            ]
        );

        $instAdmin = User::firstOrCreate(
            ['email' => 'admin@demo-university.mw', 'institution_id' => $demoInstitution->id],
            [
                'name' => 'Institution Administrator',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $instAdmin->assignRole('institution_admin');

        $qaOfficer = User::firstOrCreate(
            ['email' => 'qa@demo-university.mw', 'institution_id' => $demoInstitution->id],
            [
                'name' => 'Quality Assurance Officer',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $qaOfficer->assignRole('qa_officer');
    }
}
