<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionCatalog
{
    /** @var list<string> */
    public const SYSTEM_ROLES = [
        'system_admin',
        'nche_admin',
        'institution_admin',
        'qa_officer',
        'data_entry_officer',
        'department_head',
        'reviewer_assessor',
        'executive_management',
        'council_board',
        'external_evaluator',
        'lecturer',
        'student',
        'applicant',
    ];

    /** @var list<string> */
    public const PLATFORM_ONLY_ROLES = ['system_admin', 'nche_admin'];

    /** @var list<string> */
    public const PORTAL_AUTO_ROLES = ['student', 'applicant'];

    /** @return array<string, list<string>> */
    public static function modules(): array
    {
        return [
            'Dashboard' => ['dashboard.view'],
            'Institution' => ['institution.manage', 'institution.create'],
            'Programmes & students' => ['programme.manage', 'application.manage'],
            'Assessments' => ['assessment.create', 'assessment.score', 'assessment.review', 'assessment.approve'],
            'Evidence' => ['evidence.upload'],
            'Reports' => ['report.generate', 'report.view'],
            'Standards' => ['standards.manage'],
            'Corrective actions' => ['corrective_action.manage'],
            'User management' => ['user.manage'],
            'Teaching & LMS' => ['evaluation.view_reports', 'grade.manage', 'lms.manage', 'lms.view'],
            'Student portal' => ['student.portal'],
        ];
    }

    /** @return array<string, string> */
    public static function permissionLabels(): array
    {
        return [
            'dashboard.view' => 'View dashboard',
            'institution.manage' => 'Manage institution profile & settings',
            'institution.create' => 'Create institutions (NCHE / system)',
            'programme.manage' => 'Manage programmes, courses & students',
            'application.manage' => 'Manage admission applications',
            'assessment.create' => 'Create assessments',
            'assessment.score' => 'Score assessments',
            'assessment.review' => 'Review assessments',
            'assessment.approve' => 'Approve assessments',
            'evidence.upload' => 'Upload evidence',
            'report.generate' => 'Generate reports',
            'report.view' => 'View reports',
            'standards.manage' => 'Manage accreditation standards',
            'corrective_action.manage' => 'Manage corrective actions',
            'user.manage' => 'Manage users & roles',
            'evaluation.view_reports' => 'View teaching evaluation reports',
            'grade.manage' => 'Manage grades',
            'lms.manage' => 'Manage LMS content',
            'lms.view' => 'Access LMS as student',
            'student.portal' => 'Access student portal',
        ];
    }

    /** @return array<string, string> */
    public static function roleLabels(): array
    {
        return [
            'system_admin' => 'System administrator',
            'nche_admin' => 'NCHE / standards administrator',
            'institution_admin' => 'Institution administrator',
            'qa_officer' => 'Quality assurance officer',
            'data_entry_officer' => 'Data entry officer',
            'department_head' => 'Department head',
            'reviewer_assessor' => 'Reviewer / assessor',
            'executive_management' => 'Executive management',
            'council_board' => 'Council / board member',
            'external_evaluator' => 'External evaluator',
            'lecturer' => 'Lecturer',
            'student' => 'Student',
            'applicant' => 'Applicant',
        ];
    }

    public static function roleLabel(string $name): string
    {
        return self::roleLabels()[$name] ?? ucwords(str_replace('_', ' ', $name));
    }

    public static function isSystemRole(Role|string $role): bool
    {
        $name = $role instanceof Role ? $role->name : $role;

        return in_array($name, self::SYSTEM_ROLES, true);
    }

    public static function allPermissions(): Collection
    {
        return Permission::query()->orderBy('name')->get();
    }

    public static function allRoles(): Collection
    {
        return Role::query()->withCount('users')->orderBy('name')->get();
    }

    /** @return list<string> */
    public static function assignableRoleNames(User $actor): array
    {
        $roles = Role::query()->orderBy('name')->pluck('name')->all();

        if ($actor->hasRole('system_admin')) {
            return array_values(array_diff($roles, self::PORTAL_AUTO_ROLES));
        }

        if ($actor->hasRole('nche_admin')) {
            return array_values(array_diff($roles, ['system_admin', ...self::PORTAL_AUTO_ROLES]));
        }

        return array_values(array_filter($roles, function (string $name) {
            return ! in_array($name, [...self::PLATFORM_ONLY_ROLES, ...self::PORTAL_AUTO_ROLES], true);
        }));
    }

    /** @return list<string> */
    public static function assignablePermissions(User $actor): array
    {
        $permissions = Permission::query()->orderBy('name')->pluck('name')->all();

        if ($actor->hasRole('system_admin')) {
            return $permissions;
        }

        $restricted = ['institution.create', 'standards.manage', 'user.manage'];

        if ($actor->hasRole('nche_admin')) {
            return array_values(array_diff($permissions, ['user.manage']));
        }

        return array_values(array_diff($permissions, $restricted));
    }

    public static function canManageRole(User $actor, Role $role): bool
    {
        if (! $actor->can('user.manage')) {
            return false;
        }

        if ($actor->hasRole('system_admin')) {
            return true;
        }

        return ! self::isSystemRole($role);
    }
}
