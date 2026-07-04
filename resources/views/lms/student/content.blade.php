<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-[#0f2744]">Learning content</h2>
            <a href="{{ route('student.courses') }}" class="text-sm text-[#0f2744] hover:text-[#8cc63f]">← My courses</a>
        </div>
    </x-slot>
    <div class="py-6 max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @include('lms.partials.course-nav', ['offering' => $offering, 'active' => 'content', 'role' => 'student'])
        @forelse($modules as $module)
            <div class="bg-white rounded-lg shadow p-5">
                <h4 class="font-semibold text-[#0f2744]">{{ $module->title }}</h4>
                @if($module->description)<p class="text-sm text-gray-600 mt-2">{{ $module->description }}</p>@endif
                <ul class="mt-4 space-y-2 text-sm">
                    @foreach($module->materials as $material)
                        <li class="flex justify-between items-center border rounded-lg px-3 py-2">
                            <span>{{ $material->title }}</span>
                            @if($material->isLink())
                                <a href="{{ $material->external_url }}" target="_blank" class="text-[#0f2744] underline">Open</a>
                            @elseif($material->file_path && $material->allow_download)
                                <a href="{{ route('student.lms.materials.download', [$offering, $material]) }}" class="text-[#0f2744] underline">Download</a>
                            @else
                                <span class="text-gray-500">View only</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">No learning content published yet.</div>
        @endforelse
    </div>
</x-app-layout>
