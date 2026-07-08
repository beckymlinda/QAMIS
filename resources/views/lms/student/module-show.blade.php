<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#8cc63f]">Course LMS · Content</p>
                <h2 class="text-xl font-bold text-[#0f2744]">{{ $module->title }}</h2>
            </div>
            <a href="{{ route('student.lms.content', $offering) }}" class="text-sm font-medium text-[#0f2744] hover:text-[#8cc63f]">← Back to content</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @include('partials.alerts')
        @include('lms.partials.course-nav', ['offering' => $offering, 'active' => 'content', 'role' => 'student'])

        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
            <div class="border-b border-gray-100 bg-gradient-to-r from-[#0f2744]/5 to-transparent px-6 py-5">
                <h3 class="text-lg font-bold text-[#0f2744]">{{ $module->title }}</h3>
                @if($module->description)
                    <p class="mt-2 text-sm text-gray-600">{{ $module->description }}</p>
                @endif
                @if($module->materials->isNotEmpty())
                    <p class="mt-3 text-xs text-gray-500">
                        {{ $module->materials->count() }} {{ Str::plural('material', $module->materials->count()) }}
                        · Last updated {{ $module->materials->max('updated_at')?->format('d M Y') }}
                    </p>
                @endif
            </div>

            <div class="px-6 py-5">
                <div class="space-y-3">
                    @forelse($module->materials as $material)
                        @include('lms.partials.student-material-row', ['material' => $material, 'offering' => $offering])
                    @empty
                        <div class="rounded-xl border border-dashed border-gray-200 py-12 text-center">
                            <i class="bi bi-folder2-open text-3xl text-gray-300" aria-hidden="true"></i>
                            <p class="mt-3 text-sm text-gray-500">No materials in this module yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
