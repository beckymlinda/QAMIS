<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium">Full name *</label>
        <input name="name" value="{{ old('name', $managedUser?->name) }}" required class="mt-1 w-full rounded-md border-gray-300">
        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium">Email *</label>
        <input type="email" name="email" value="{{ old('email', $managedUser?->email) }}" required class="mt-1 w-full rounded-md border-gray-300">
        @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="block text-sm font-medium">Password {{ $managedUser ? '' : '*' }}</label>
            <input type="password" name="password" {{ $managedUser ? '' : 'required' }} autocomplete="new-password" class="mt-1 w-full rounded-md border-gray-300">
            @if($managedUser)
                <p class="mt-1 text-xs text-gray-500">Leave blank to keep the current password.</p>
            @endif
            @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Confirm password {{ $managedUser ? '' : '*' }}</label>
            <input type="password" name="password_confirmation" {{ $managedUser ? '' : 'required' }} autocomplete="new-password" class="mt-1 w-full rounded-md border-gray-300">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium">Role *</label>
        <select name="role" required class="mt-1 w-full rounded-md border-gray-300">
            <option value="">Select role</option>
            @foreach($roles as $role)
                <option value="{{ $role->name }}" @selected(old('role', $selectedRole) === $role->name)>
                    {{ \App\Support\PermissionCatalog::roleLabel($role->name) }}
                </option>
            @endforeach
        </select>
        @error('role')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    @if($institutions->isNotEmpty())
        <div>
            <label class="block text-sm font-medium">Institution</label>
            <select name="institution_id" class="mt-1 w-full rounded-md border-gray-300">
                <option value="">Platform (no institution)</option>
                @foreach($institutions as $institution)
                    <option value="{{ $institution->id }}" @selected(old('institution_id', $managedUser?->institution_id ?? $defaultInstitutionId) == $institution->id)>
                        {{ $institution->name }}
                    </option>
                @endforeach
            </select>
            @error('institution_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    @endif

    <div>
        <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $managedUser?->is_active ?? true)) class="rounded border-gray-300">
            <span class="text-sm font-medium">Account is active</span>
        </label>
    </div>
</div>
