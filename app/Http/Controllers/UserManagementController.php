<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\User;
use App\Support\InstitutionScope;
use App\Support\PermissionCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $actor = $request->user();
        $institutionId = InstitutionScope::institutionId();

        $users = User::query()
            ->with('roles', 'institution')
            ->when(! $actor->isNcheOrSystemAdmin(), fn ($q) => $q->where('institution_id', $actor->institution_id))
            ->when($actor->isNcheOrSystemAdmin() && $request->integer('institution_id'), fn ($q, $id) => $q->where('institution_id', $id))
            ->when($request->string('role')->toString(), function ($q, $role) {
                $q->whereHas('roles', fn ($r) => $r->where('name', $role));
            })
            ->when($request->string('search')->toString(), function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->string('status')->toString() === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->string('status')->toString() === 'active', fn ($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        $roles = Role::query()
            ->whereIn('name', PermissionCatalog::assignableRoleNames($actor))
            ->orderBy('name')
            ->get();

        $institutions = $actor->isNcheOrSystemAdmin()
            ? Institution::query()->orderBy('name')->get(['id', 'name'])
            : collect();

        return view('users.index', compact('users', 'roles', 'institutions', 'institutionId'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', User::class);

        return view('users.create', $this->formData($request->user()));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $actor = $request->user();
        $validated = $this->validatedUser($request, $actor);

        $user = User::create([
            'institution_id' => $validated['institution_id'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $user->syncRoles([$validated['role']]);

        return redirect()->route('users.index')->with('success', 'User account created.');
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('users.edit', array_merge(
            ['managedUser' => $user],
            $this->formData(auth()->user(), $user)
        ));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $actor = $request->user();
        $validated = $this->validatedUser($request, $actor, $user);

        $user->update([
            'institution_id' => $validated['institution_id'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        if (! empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        $user->syncRoles([$validated['role']]);

        return redirect()->route('users.index')->with('success', 'User account updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User account removed.');
    }

    /** @return array<string, mixed> */
    protected function formData(User $actor, ?User $managedUser = null): array
    {
        return [
            'roles' => Role::query()
                ->whereIn('name', PermissionCatalog::assignableRoleNames($actor))
                ->orderBy('name')
                ->get(),
            'institutions' => $actor->isNcheOrSystemAdmin()
                ? Institution::query()->orderBy('name')->get(['id', 'name'])
                : collect(),
            'defaultInstitutionId' => $actor->institution_id,
            'selectedRole' => old('role', $managedUser?->roles->first()?->name),
        ];
    }

    /** @return array<string, mixed> */
    protected function validatedUser(Request $request, User $actor, ?User $managedUser = null): array
    {
        $institutionId = $actor->isNcheOrSystemAdmin()
            ? $request->integer('institution_id') ?: null
            : $actor->institution_id;

        $emailRule = Rule::unique('users', 'email')
            ->where(fn ($q) => $q->where('institution_id', $institutionId));

        if ($managedUser) {
            $emailRule->ignore($managedUser->id);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', $emailRule],
            'password' => [$managedUser ? 'nullable' : 'required', 'confirmed', Password::defaults()],
            'role' => ['required', 'string', Rule::in(PermissionCatalog::assignableRoleNames($actor))],
            'institution_id' => ['nullable', 'integer', 'exists:institutions,id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        abort_unless(in_array($validated['role'], PermissionCatalog::assignableRoleNames($actor), true), 403, 'You cannot assign that role.');

        $validated['institution_id'] = $institutionId;

        return $validated;
    }
}
