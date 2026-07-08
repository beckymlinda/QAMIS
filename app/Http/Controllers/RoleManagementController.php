<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\PermissionCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleManagementController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $roles = PermissionCatalog::allRoles()->load('permissions');

        return view('roles.index', compact('roles'));
    }

    public function create(): View
    {
        $this->authorize('viewAny', User::class);

        return view('roles.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', User::class);

        $validated = $this->validatedRole($request);

        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);
        $role->syncPermissions($validated['permissions']);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('roles.index')->with('success', 'Role created.');
    }

    public function edit(Role $role): View
    {
        $this->authorize('viewAny', User::class);
        abort_unless(PermissionCatalog::canManageRole(auth()->user(), $role), 403, 'System roles can only be edited by a system administrator.');

        $role->load('permissions');

        return view('roles.edit', array_merge(['role' => $role], $this->formData($role)));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->authorize('viewAny', User::class);
        abort_unless(PermissionCatalog::canManageRole(auth()->user(), $role), 403);

        $validated = $this->validatedRole($request, $role);

        if (PermissionCatalog::isSystemRole($role)) {
            $role->syncPermissions($validated['permissions']);
        } else {
            $role->update(['name' => $validated['name']]);
            $role->syncPermissions($validated['permissions']);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('viewAny', User::class);
        abort_unless(PermissionCatalog::canManageRole(auth()->user(), $role), 403);
        abort_if(PermissionCatalog::isSystemRole($role), 403, 'Built-in roles cannot be deleted.');

        abort_if($role->users()->exists(), 422, 'Remove this role from all users before deleting it.');

        $role->delete();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('roles.index')->with('success', 'Role deleted.');
    }

    /** @return array<string, mixed> */
    protected function formData(?Role $role = null): array
    {
        $actor = auth()->user();
        $assignable = PermissionCatalog::assignablePermissions($actor);
        $selected = old('permissions', $role?->permissions->pluck('name')->all() ?? []);

        return [
            'modules' => PermissionCatalog::modules(),
            'permissionLabels' => PermissionCatalog::permissionLabels(),
            'assignablePermissions' => $assignable,
            'selectedPermissions' => $selected,
            'isSystemRole' => $role ? PermissionCatalog::isSystemRole($role) : false,
        ];
    }

    /** @return array<string, mixed> */
    protected function validatedRole(Request $request, ?Role $role = null): array
    {
        $actor = $request->user();
        $assignable = PermissionCatalog::assignablePermissions($actor);

        $nameRules = ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/'];

        if ($role && PermissionCatalog::isSystemRole($role)) {
            $nameRules = ['prohibited'];
        } else {
            $nameRules[] = Rule::unique('roles', 'name')->ignore($role?->id);
            $nameRules[] = Rule::notIn(PermissionCatalog::SYSTEM_ROLES);
        }

        $validated = $request->validate([
            'name' => $nameRules,
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in($assignable)],
        ]);

        $validated['permissions'] = array_values(array_intersect($validated['permissions'] ?? [], $assignable));

        if ($role && PermissionCatalog::isSystemRole($role)) {
            $validated['name'] = $role->name;
        }

        return $validated;
    }
}
