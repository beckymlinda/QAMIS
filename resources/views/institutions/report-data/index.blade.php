<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-[#0f2744]">Report Data</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $institution->name }} — information for self-assessment reports</p>
            </div>
            <a href="{{ route('institutions.show', $institution) }}" class="text-sm font-medium text-[#0f2744] hover:text-[#8cc63f]">← Institution overview</a>
        </div>
    </x-slot>

    <div class="py-4 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6" x-data="{ tab: '{{ request('tab', 'narrative') }}' }">
        @include('partials.alerts')

        <div class="bg-[#0f2744] text-white rounded-lg p-5 shadow text-sm leading-relaxed">
            Complete these sections to populate your Self-Assessment Report (SAR). After saving, generate the report from <a href="{{ route('reports.index') }}" class="underline text-[#8cc63f]">Reports</a>.
        </div>

        <div class="flex flex-wrap gap-2">
            @foreach([
                'narrative' => '1. Report narrative',
                'background' => '2. Background & SWOT',
                'governance' => '3. Governance',
                'policies' => '4. Policies',
                'staff' => '5. Staff',
                'students' => '6. Students',
            ] as $key => $label)
                <button type="button" @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}' ? 'bg-[#0f2744] text-white' : 'bg-white text-[#0f2744] border border-gray-200'"
                    class="px-3 py-2 rounded-md text-sm font-medium">{{ $label }}</button>
            @endforeach
        </div>

        <form method="POST" action="{{ route('institutions.report-data.update', $institution) }}" class="space-y-6">
            @csrf @method('PUT')

            <div x-show="tab === 'narrative'" x-cloak class="bg-white p-6 rounded-lg shadow space-y-4">
                <h3 class="text-lg font-semibold text-[#0f2744]">Executive summary & introduction</h3>
                <p class="text-sm text-gray-500">Maps to Executive Summary, List of Abbreviations, and Section 1.0 Introduction in the SAR template.</p>
                <div>
                    <label class="block text-sm font-medium">Executive Summary</label>
                    <textarea name="executive_summary" rows="5" class="mt-1 w-full rounded-md border-gray-300" placeholder="Summarise institutional capacity, key findings, and readiness...">{{ old('executive_summary', $profile->executive_summary) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium">List of Abbreviations and Acronyms</label>
                    <textarea name="abbreviations_acronyms" rows="4" class="mt-1 w-full rounded-md border-gray-300" placeholder="NCHE — National Council for Higher Education&#10;HEI — Higher Education Institution">{{ old('abbreviations_acronyms', $profile->abbreviations_acronyms) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium">1.1 Approach</label>
                    <textarea name="introduction_approach" rows="4" class="mt-1 w-full rounded-md border-gray-300" placeholder="Describe how the self-assessment was conducted...">{{ old('introduction_approach', $profile->introduction_approach) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium">1.2 Composition of the Assessment Team</label>
                    <textarea name="assessment_team_composition" rows="4" class="mt-1 w-full rounded-md border-gray-300" placeholder="List team members, roles, and qualifications...">{{ old('assessment_team_composition', $profile->assessment_team_composition) }}</textarea>
                </div>
                <button type="submit" class="px-4 py-2 bg-[#8cc63f] text-[#0f2744] font-semibold rounded-md">Save narrative</button>
            </div>

            <div x-show="tab === 'background'" x-cloak class="bg-white p-6 rounded-lg shadow space-y-4">
                <h3 class="text-lg font-semibold text-[#0f2744]">Institutional background</h3>
                <p class="text-sm text-gray-500">Section 2.0 — vision, mission, core values, and core function.</p>
                <div class="grid md:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium">Vision (2.2.1)</label><textarea name="vision" rows="3" class="mt-1 w-full rounded-md border-gray-300">{{ old('vision', $profile->vision) }}</textarea></div>
                    <div><label class="block text-sm font-medium">Mission (2.2.2)</label><textarea name="mission" rows="3" class="mt-1 w-full rounded-md border-gray-300">{{ old('mission', $profile->mission) }}</textarea></div>
                </div>
                <div><label class="block text-sm font-medium">Core Values (2.2.3)</label><textarea name="core_values" rows="3" class="mt-1 w-full rounded-md border-gray-300">{{ old('core_values', $profile->core_values) }}</textarea></div>
                <div><label class="block text-sm font-medium">Core Function of the HEI (2.1)</label><textarea name="core_function" rows="3" class="mt-1 w-full rounded-md border-gray-300">{{ old('core_function', $profile->core_function) }}</textarea></div>
                <div><label class="block text-sm font-medium">Institutional background narrative</label><textarea name="background_narrative" rows="4" class="mt-1 w-full rounded-md border-gray-300">{{ old('background_narrative', $profile->background_narrative) }}</textarea></div>
                <div class="grid md:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium">Contact email</label><input name="email" class="mt-1 w-full rounded-md border-gray-300" value="{{ old('email', $contact->email) }}"></div>
                    <div><label class="block text-sm font-medium">Telephone</label><input name="telephone" class="mt-1 w-full rounded-md border-gray-300" value="{{ old('telephone', $contact->telephone) }}"></div>
                </div>
                <h4 class="font-semibold pt-2">3.0 SWOT Analysis</h4>
                @foreach (['strengths','weaknesses','opportunities','threats'] as $swot)
                    <div><label class="block text-sm font-medium capitalize">{{ $swot }}</label><textarea name="swot_{{ $swot }}" rows="2" class="mt-1 w-full rounded-md border-gray-300">{{ old('swot_'.$swot, $profile->swot_analysis[$swot] ?? '') }}</textarea></div>
                @endforeach
                <button type="submit" class="px-4 py-2 bg-[#8cc63f] text-[#0f2744] font-semibold rounded-md">Save background</button>
            </div>

            <div x-show="tab === 'policies'" x-cloak class="bg-white p-6 rounded-lg shadow space-y-4">
                <h3 class="text-lg font-semibold text-[#0f2744]">2.4 Policies, Procedures, and Guidelines</h3>
                <textarea name="policies_procedures_summary" rows="8" class="w-full rounded-md border-gray-300" placeholder="Summarise governing policies and evidence of implementation...">{{ old('policies_procedures_summary', $profile->policies_procedures_summary) }}</textarea>
                <button type="submit" class="px-4 py-2 bg-[#8cc63f] text-[#0f2744] font-semibold rounded-md">Save policies</button>
            </div>
        </form>

        <div x-show="tab === 'governance'" x-cloak class="space-y-6">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-[#0f2744] mb-4">2.3 Governance Systems</h3>
                @foreach($governanceBodyTypes as $type => $label)
                    <div class="mb-6">
                        <h4 class="font-medium text-[#0f2744] mb-2">{{ $label }}</h4>
                        @if(($governanceByBody[$type] ?? collect())->isNotEmpty())
                            <table class="min-w-full text-sm mb-3">
                                <thead class="bg-gray-50"><tr>
                                    <th class="px-3 py-2 text-left">Name</th>
                                    <th class="px-3 py-2 text-left">Gender</th>
                                    <th class="px-3 py-2 text-left">Qualification</th>
                                    <th class="px-3 py-2 text-left">Designation</th>
                                    <th class="px-3 py-2"></th>
                                </tr></thead>
                                <tbody>
                                    @foreach($governanceByBody[$type] as $member)
                                        <tr class="border-t">
                                            <td class="px-3 py-2">{{ $member->name }}</td>
                                            <td class="px-3 py-2">{{ ucfirst($member->gender ?? '—') }}</td>
                                            <td class="px-3 py-2">{{ $member->qualification ?? '—' }}</td>
                                            <td class="px-3 py-2">{{ $member->designation ?? '—' }}</td>
                                            <td class="px-3 py-2 text-right">
                                                <form method="POST" action="{{ route('institutions.report-data.governance.destroy', [$institution, $member]) }}" onsubmit="return confirm('Remove this member?');">@csrf @method('DELETE')
                                                    <button type="submit" class="text-red-600 text-xs">Remove</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-sm text-gray-500 mb-2">No members recorded yet.</p>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h4 class="font-medium mb-3">Add governance member</h4>
                <form method="POST" action="{{ route('institutions.report-data.governance.store', $institution) }}" class="grid md:grid-cols-2 gap-4">
                    @csrf
                    <div><label class="block text-sm font-medium">Body</label>
                        <select name="body_type" class="mt-1 w-full rounded-md border-gray-300" required>
                            @foreach($governanceBodyTypes as $type => $label)<option value="{{ $type }}">{{ $label }}</option>@endforeach
                        </select>
                    </div>
                    <div><label class="block text-sm font-medium">Name</label><input name="name" class="mt-1 w-full rounded-md border-gray-300" required></div>
                    <div><label class="block text-sm font-medium">Gender</label>
                        <select name="gender" class="mt-1 w-full rounded-md border-gray-300"><option value="">—</option><option value="male">Male</option><option value="female">Female</option></select>
                    </div>
                    <div><label class="block text-sm font-medium">Designation</label><input name="designation" class="mt-1 w-full rounded-md border-gray-300"></div>
                    <div><label class="block text-sm font-medium">Highest qualification</label><input name="qualification" class="mt-1 w-full rounded-md border-gray-300"></div>
                    <div><label class="block text-sm font-medium">Awarding institution</label><input name="awarding_institution" class="mt-1 w-full rounded-md border-gray-300"></div>
                    <div class="md:col-span-2"><button type="submit" class="px-4 py-2 bg-[#0f2744] text-white rounded-md text-sm">Add member</button></div>
                </form>
            </div>
        </div>

        <div x-show="tab === 'staff'" x-cloak class="space-y-6">
            <div class="bg-white p-6 rounded-lg shadow overflow-x-auto">
                <h3 class="text-lg font-semibold text-[#0f2744] mb-4">2.5 Staff profile (by programme)</h3>
                @if($institution->staffMembers->isNotEmpty())
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50"><tr>
                            <th class="px-3 py-2 text-left">Name</th>
                            <th class="px-3 py-2 text-left">Programme</th>
                            <th class="px-3 py-2 text-left">Gender</th>
                            <th class="px-3 py-2 text-left">Qualification</th>
                            <th class="px-3 py-2 text-left">Rank</th>
                            <th class="px-3 py-2 text-left">Employment</th>
                            <th></th>
                        </tr></thead>
                        <tbody>
                            @foreach($institution->staffMembers as $staff)
                                <tr class="border-t">
                                    <td class="px-3 py-2">{{ $staff->name }}</td>
                                    <td class="px-3 py-2">{{ $staff->programme?->name ?? 'Institution-wide' }}</td>
                                    <td class="px-3 py-2">{{ ucfirst($staff->gender ?? '—') }}</td>
                                    <td class="px-3 py-2">{{ $staff->qualification ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ $staff->rank ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ ucfirst(str_replace('-', ' ', $staff->employment_type ?? '—')) }}</td>
                                    <td class="px-3 py-2 text-right">
                                        <form method="POST" action="{{ route('institutions.report-data.staff.destroy', [$institution, $staff]) }}" onsubmit="return confirm('Remove?');">@csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 text-xs">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-sm text-gray-500">No staff records yet. Add academic and administrative staff below.</p>
                @endif
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h4 class="font-medium mb-3">Add staff member</h4>
                <form method="POST" action="{{ route('institutions.report-data.staff.store', $institution) }}" class="grid md:grid-cols-2 gap-4">
                    @csrf
                    <div><label class="block text-sm font-medium">Name</label><input name="name" class="mt-1 w-full rounded-md border-gray-300" required></div>
                    <div><label class="block text-sm font-medium">Type</label>
                        <select name="type" class="mt-1 w-full rounded-md border-gray-300"><option value="academic">Academic</option><option value="administrative">Administrative</option></select>
                    </div>
                    <div><label class="block text-sm font-medium">Programme (optional)</label>
                        <select name="programme_id" class="mt-1 w-full rounded-md border-gray-300"><option value="">Institution-wide</option>
                            @foreach($institution->programmes as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                        </select>
                    </div>
                    <div><label class="block text-sm font-medium">Gender</label>
                        <select name="gender" class="mt-1 w-full rounded-md border-gray-300"><option value="">—</option><option value="male">Male</option><option value="female">Female</option></select>
                    </div>
                    <div><label class="block text-sm font-medium">Highest qualification</label><input name="qualification" class="mt-1 w-full rounded-md border-gray-300"></div>
                    <div><label class="block text-sm font-medium">Awarding institution</label><input name="awarding_institution" class="mt-1 w-full rounded-md border-gray-300"></div>
                    <div><label class="block text-sm font-medium">Year of award</label><input type="number" name="qualification_year" class="mt-1 w-full rounded-md border-gray-300" min="1950" max="2100"></div>
                    <div><label class="block text-sm font-medium">Rank / position</label><input name="rank" class="mt-1 w-full rounded-md border-gray-300"></div>
                    <div><label class="block text-sm font-medium">Employment type</label>
                        <select name="employment_type" class="mt-1 w-full rounded-md border-gray-300"><option value="">—</option><option value="full-time">Full-time</option><option value="part-time">Part-time</option></select>
                    </div>
                    <div class="md:col-span-2"><button type="submit" class="px-4 py-2 bg-[#0f2744] text-white rounded-md text-sm">Add staff member</button></div>
                </form>
            </div>
        </div>

        <div x-show="tab === 'students'" x-cloak class="space-y-6">
            <div class="bg-white p-6 rounded-lg shadow overflow-x-auto">
                <h3 class="text-lg font-semibold text-[#0f2744] mb-4">2.6 Student profile (by sex and programme)</h3>
                @if($institution->studentEnrolments->isNotEmpty())
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50"><tr>
                            <th class="px-3 py-2 text-left">Programme / Qualification</th>
                            <th class="px-3 py-2 text-left">Male</th>
                            <th class="px-3 py-2 text-left">Female</th>
                            <th class="px-3 py-2 text-left">Total</th>
                            <th></th>
                        </tr></thead>
                        <tbody>
                            @foreach($institution->studentEnrolments as $row)
                                <tr class="border-t">
                                    <td class="px-3 py-2">{{ $row->programme?->name ?? $row->qualification_type }}</td>
                                    <td class="px-3 py-2">{{ $row->male_count }}</td>
                                    <td class="px-3 py-2">{{ $row->female_count }}</td>
                                    <td class="px-3 py-2 font-medium">{{ $row->male_count + $row->female_count }}</td>
                                    <td class="px-3 py-2 text-right">
                                        <form method="POST" action="{{ route('institutions.report-data.students.destroy', [$institution, $row]) }}" onsubmit="return confirm('Remove?');">@csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 text-xs">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-sm text-gray-500">No student enrolment data yet.</p>
                @endif
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h4 class="font-medium mb-3">Add student enrolment row</h4>
                <form method="POST" action="{{ route('institutions.report-data.students.store', $institution) }}" class="grid md:grid-cols-2 gap-4">
                    @csrf
                    <div><label class="block text-sm font-medium">Programme</label>
                        <select name="programme_id" class="mt-1 w-full rounded-md border-gray-300"><option value="">General / not programme-specific</option>
                            @foreach($institution->programmes as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                        </select>
                    </div>
                    <div><label class="block text-sm font-medium">Qualification type</label><input name="qualification_type" class="mt-1 w-full rounded-md border-gray-300" placeholder="e.g. Bachelor's degree" required></div>
                    <div><label class="block text-sm font-medium">Male count</label><input type="number" name="male_count" value="0" min="0" class="mt-1 w-full rounded-md border-gray-300" required></div>
                    <div><label class="block text-sm font-medium">Female count</label><input type="number" name="female_count" value="0" min="0" class="mt-1 w-full rounded-md border-gray-300" required></div>
                    <div class="md:col-span-2"><button type="submit" class="px-4 py-2 bg-[#0f2744] text-white rounded-md text-sm">Add enrolment row</button></div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
