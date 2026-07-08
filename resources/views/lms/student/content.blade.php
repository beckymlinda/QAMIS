<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#8cc63f]">Course LMS</p>
                <h2 class="text-xl font-bold text-[#0f2744]">Learning Content</h2>
            </div>
            <a href="{{ route('student.courses') }}" class="text-sm font-medium text-[#0f2744] hover:text-[#8cc63f]">← My courses</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @include('partials.alerts')
        @include('lms.partials.course-nav', ['offering' => $offering, 'active' => 'content', 'role' => 'student'])

        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
            <div class="border-b border-gray-100 px-6 py-4">
                <p class="text-sm text-gray-600">Browse modules and open materials. Upload dates show when each item was added by your lecturer.</p>
            </div>
            <table class="min-w-full text-sm">
                <thead class="border-b border-gray-100 bg-gray-50/80">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Module</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Materials</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Last upload</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($modules as $module)
                        @php
                            $latestMaterial = $module->materials->sortByDesc('created_at')->first();
                        @endphp
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-6 py-4">
                                <p class="font-medium text-[#0f2744]">{{ $module->title }}</p>
                                @if($module->description)
                                    <p class="mt-1 line-clamp-1 text-xs text-gray-500">{{ $module->description }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full bg-[#0f2744]/5 px-2.5 py-1 text-xs font-semibold text-[#0f2744]">
                                    {{ $module->materials->count() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                @if($latestMaterial)
                                    <span class="inline-flex items-center gap-1 text-xs">
                                        <i class="bi bi-calendar3 text-[#8cc63f]" aria-hidden="true"></i>
                                        {{ $latestMaterial->created_at->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end">
                                    <a href="{{ route('student.lms.modules.show', [$offering, $module]) }}" class="inline-flex items-center gap-1 rounded-lg bg-gray-50 px-3 py-1.5 text-xs font-semibold text-[#0f2744] ring-1 ring-gray-200 hover:bg-gray-100">
                                        <i class="bi bi-eye" aria-hidden="true"></i> View
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center text-gray-500">No learning content published yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
