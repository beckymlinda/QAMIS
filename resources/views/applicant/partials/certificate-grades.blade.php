@php
    use App\Support\CertificateGrades;
    $coreSubjects = CertificateGrades::coreSubjects();
    $optionalSubjects = CertificateGrades::optionalSubjects();
    $core = $gradeData['core'] ?? CertificateGrades::defaultGradeMap();
    $extra = old('extra_grades', $gradeData['extra'] ?? []);
@endphp

<div class="space-y-4" x-data="{
    extraRows: @js(array_values($extra)),
    optionalSubjects: @js($optionalSubjects),
    addExtra(preset = '') {
        this.extraRows.push({ subject: preset, points: '' });
    },
    removeExtra(index) {
        this.extraRows.splice(index, 1);
    },
    bump(row, delta) {
        const current = parseInt(row.points || 0, 10) || 0;
        row.points = Math.min(9, Math.max(1, current + delta));
    },
    bumpCore(name, delta) {
        const input = $refs[name];
        if (!input) return;
        const current = parseInt(input.value || 0, 10) || 0;
        input.value = Math.min(9, Math.max(1, current + delta));
    }
}">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h3 class="text-base font-bold text-gray-900">MSCE / certificate grades</h3>
            <p class="text-xs text-gray-500">Enter points (1–9) for each subject on your certificate.</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-4 py-3 font-semibold">Subject</th>
                    <th class="px-4 py-3 font-semibold w-40">Points</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @foreach($coreSubjects as $subject)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $subject }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1">
                                <button type="button" @click="bumpCore('grade_{{ Str::slug($subject) }}', -1)" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-gray-600 hover:bg-gray-100" aria-label="Decrease">−</button>
                                <input
                                    type="number"
                                    name="grades[{{ $subject }}]"
                                    x-ref="grade_{{ Str::slug($subject) }}"
                                    value="{{ old('grades.'.$subject, $core[$subject] ?? '') }}"
                                    min="1"
                                    max="9"
                                    step="1"
                                    required
                                    class="w-full rounded-lg border-gray-300 text-center font-semibold"
                                >
                                <button type="button" @click="bumpCore('grade_{{ Str::slug($subject) }}', 1)" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-gray-600 hover:bg-gray-100" aria-label="Increase">+</button>
                            </div>
                        </td>
                    </tr>
                @endforeach

                <template x-for="(row, index) in extraRows" :key="index">
                    <tr>
                        <td class="px-4 py-3">
                            <input type="text" :name="'extra_grades[' + index + '][subject]'" x-model="row.subject" placeholder="Subject name" class="w-full rounded-lg border-gray-300" required>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1">
                                <button type="button" @click="bump(row, -1)" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-gray-600 hover:bg-gray-100">−</button>
                                <input type="number" :name="'extra_grades[' + index + '][points]'" x-model="row.points" min="1" max="9" step="1" class="w-full rounded-lg border-gray-300 text-center font-semibold">
                                <button type="button" @click="bump(row, 1)" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-gray-600 hover:bg-gray-100">+</button>
                                <button type="button" @click="removeExtra(index)" class="ml-1 text-xs font-semibold text-red-600 hover:text-red-800">Remove</button>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <div class="flex flex-wrap gap-2">
        <button type="button" @click="addExtra('')" class="inline-flex items-center gap-1.5 rounded-xl border border-dashed border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-brand-primary hover:border-brand-primary hover:bg-brand-primary/5">
            <i class="bi bi-plus-lg"></i> Add another subject
        </button>
        @foreach($optionalSubjects as $optional)
            <button type="button" @click="addExtra(@js($optional))" class="rounded-xl bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-200">{{ $optional }}</button>
        @endforeach
    </div>
</div>
