@props(['student' => null, 'institution', 'programmes', 'selectedProgrammeId' => null, 'defaultEmailExample' => null])

<div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3 text-sm">
    <div class="sm:col-span-2">
        <label class="block text-gray-700 font-medium mb-1">Programme <span class="text-red-500">*</span></label>
        <select name="programme_id" required class="w-full rounded-md border-gray-300 shadow-sm">
            <option value="">Select programme</option>
            @foreach($programmes as $programme)
                <option value="{{ $programme->id }}" @selected(old('programme_id', $student?->programme_id ?? $selectedProgrammeId) == $programme->id)>
                    {{ $programme->name }}
                </option>
            @endforeach
        </select>
        @error('programme_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-gray-700 font-medium mb-1">First name <span class="text-red-500">*</span></label>
        <input name="first_name" value="{{ old('first_name', $student?->first_name) }}" required class="w-full rounded-md border-gray-300 shadow-sm">
        @error('first_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-gray-700 font-medium mb-1">Last name <span class="text-red-500">*</span></label>
        <input name="last_name" value="{{ old('last_name', $student?->last_name) }}" required class="w-full rounded-md border-gray-300 shadow-sm">
        @error('last_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-gray-700 font-medium mb-1">Phone</label>
        <input name="phone" value="{{ old('phone', $student?->phone) }}" class="w-full rounded-md border-gray-300 shadow-sm" placeholder="+265...">
        @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-gray-700 font-medium mb-1">Student number</label>
        <input name="student_number" value="{{ old('student_number', $student?->student_number) }}" class="w-full rounded-md border-gray-300 shadow-sm" @if(!$student) placeholder="Leave blank to auto-generate" @else required @endif>
        @error('student_number')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-gray-700 font-medium mb-1">Email (portal login) @if(!$student)<span class="text-gray-400 font-normal">optional</span>@else<span class="text-red-500">*</span>@endif</label>
        <input name="email" type="email" value="{{ old('email', $student?->email) }}" @if($student) required @endif class="w-full rounded-md border-gray-300 shadow-sm" placeholder="{{ $student ? '' : ($defaultEmailExample ? 'Leave blank for '.$defaultEmailExample : 'Leave blank to auto-generate') }}">
        @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-gray-700 font-medium mb-1">Year of study <span class="text-red-500">*</span></label>
        <input name="year_of_study" type="number" min="1" max="8" value="{{ old('year_of_study', $student?->year_of_study ?? 1) }}" required class="w-full rounded-md border-gray-300 shadow-sm">
        @error('year_of_study')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    @if($student)
        <div>
            <label class="block text-gray-700 font-medium mb-1">Status <span class="text-red-500">*</span></label>
            <select name="status" required class="w-full rounded-md border-gray-300 shadow-sm">
                @foreach(['active', 'inactive', 'graduated', 'suspended'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $student->status) === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            @error('status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
    @endif

    <div @class(['sm:col-span-2' => $student])>
        <label class="block text-gray-700 font-medium mb-1">Portal password <span class="text-gray-400 font-normal">(optional)</span></label>
        <input name="password" type="password" class="w-full rounded-md border-gray-300 shadow-sm sm:max-w-xs" placeholder="{{ $student ? 'Leave blank to keep current password' : 'Leave blank for default password' }}">
        @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
</div>
