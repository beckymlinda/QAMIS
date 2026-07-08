@foreach($modules as $module => $permissions)
    @php
        $modulePermissions = array_values(array_intersect($permissions, $assignablePermissions));
    @endphp
    @if(count($modulePermissions) === 0)
        @continue
    @endif
    <div class="rounded-xl border border-gray-200 p-4">
        <h4 class="text-sm font-bold text-brand-primary">{{ $module }}</h4>
        <div class="mt-3 grid gap-2 sm:grid-cols-2">
            @foreach($modulePermissions as $permission)
                <label class="inline-flex items-start gap-2 rounded-lg bg-gray-50 px-3 py-2 text-sm">
                    <input type="checkbox" name="permissions[]" value="{{ $permission }}" @checked(in_array($permission, $selectedPermissions, true)) class="mt-0.5 rounded border-gray-300">
                    <span>{{ $permissionLabels[$permission] ?? $permission }}</span>
                </label>
            @endforeach
        </div>
    </div>
@endforeach
