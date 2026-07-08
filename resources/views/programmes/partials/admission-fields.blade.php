@php($programme = $programme ?? null)
<div class="border-t border-gray-200 pt-4 mt-4">
    <h3 class="text-sm font-bold text-gray-800">Academic &amp; fees</h3>
    <div class="mt-4 grid gap-4 sm:grid-cols-2">
        <div>
            <label class="block text-sm font-medium">Total credit hours</label>
            <input type="number" step="0.1" name="total_credit_hours" value="{{ old('total_credit_hours', $programme?->total_credit_hours) }}" class="mt-1 w-full rounded-md border-gray-300">
            <p class="mt-1 text-xs text-gray-500">Used for programme-level GPA weighting.</p>
        </div>
        <div>
            <label class="block text-sm font-medium">Duration</label>
            <input name="duration" value="{{ old('duration', $programme?->duration) }}" placeholder="e.g. 4 years" class="mt-1 w-full rounded-md border-gray-300">
        </div>
        <div>
            <label class="block text-sm font-medium">Tuition fee (MK)</label>
            <input type="number" step="0.01" name="tuition_fee" value="{{ old('tuition_fee', $programme?->tuition_fee) }}" class="mt-1 w-full rounded-md border-gray-300">
        </div>
        <div>
            <label class="block text-sm font-medium">Application fee (MK)</label>
            <input type="number" step="0.01" name="application_fee" value="{{ old('application_fee', $programme?->application_fee) }}" class="mt-1 w-full rounded-md border-gray-300">
        </div>
        <div>
            <label class="block text-sm font-medium">Registration fee (MK)</label>
            <input type="number" step="0.01" name="registration_fee" value="{{ old('registration_fee', $programme?->registration_fee) }}" class="mt-1 w-full rounded-md border-gray-300">
        </div>
        <div>
            <label class="block text-sm font-medium">Other fees (MK)</label>
            <input type="number" step="0.01" name="other_fees" value="{{ old('other_fees', $programme?->other_fees) }}" class="mt-1 w-full rounded-md border-gray-300">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium">Short description</label>
            <textarea name="description" rows="2" class="mt-1 w-full rounded-md border-gray-300">{{ old('description', $programme?->description) }}</textarea>
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium">Entry requirements</label>
            <textarea name="entry_requirements" rows="3" class="mt-1 w-full rounded-md border-gray-300">{{ old('entry_requirements', $programme?->entry_requirements) }}</textarea>
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium">Required grades</label>
            <textarea name="required_grades" rows="2" class="mt-1 w-full rounded-md border-gray-300">{{ old('required_grades', $programme?->required_grades) }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium">Maximum intake</label>
            <input type="number" name="max_intake" value="{{ old('max_intake', $programme?->max_intake) }}" class="mt-1 w-full rounded-md border-gray-300">
        </div>
        <div>
            <label class="block text-sm font-medium">Application closing date</label>
            <input type="date" name="application_closing_date" value="{{ old('application_closing_date', $programme?->application_closing_date?->format('Y-m-d')) }}" class="mt-1 w-full rounded-md border-gray-300">
        </div>
        <div class="sm:col-span-2">
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="applications_open" value="1" @checked(old('applications_open', $programme?->applications_open ?? false)) class="rounded border-gray-300">
                <span class="text-sm font-medium">Applications open for this programme</span>
            </label>
        </div>
    </div>
</div>
