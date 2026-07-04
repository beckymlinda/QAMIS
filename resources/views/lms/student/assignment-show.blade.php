<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-[#0f2744]">{{ $assignment->title }}</h2>
            <a href="{{ route('student.lms.assignments', $offering) }}" class="text-sm text-[#0f2744] hover:text-[#8cc63f]">← Assignments</a>
        </div>
    </x-slot>
    <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @include('partials.alerts')
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <div class="flex flex-wrap gap-3 text-xs">
                <span class="rounded-full bg-amber-50 px-3 py-1 font-medium text-amber-800"><i class="bi bi-calendar-event"></i> Due date: {{ $assignment->due_at?->format('d M Y H:i') ?? 'No deadline' }}</span>
                <span class="rounded-full bg-blue-50 px-3 py-1 font-medium text-blue-800"><i class="bi bi-award"></i> Max grade: {{ $assignment->max_score }}</span>
            </div>
            <div class="mt-4 text-sm whitespace-pre-wrap text-gray-700">{{ $assignment->instructions ?: 'No additional instructions.' }}</div>
            @if($assignment->hasAttachment())
                <p class="mt-4 text-sm text-gray-600"><i class="bi bi-file-earmark-pdf"></i> Assignment includes a PDF attachment — ask your lecturer if you need a copy.</p>
            @endif
        </div>

        @if($submission?->isGraded())
            <div class="rounded-2xl border border-green-200 bg-green-50 p-5">
                <p class="font-semibold text-green-800">Grade: {{ $submission->score }}/{{ $assignment->max_score }}</p>
                @if($submission->feedback)<p class="mt-2 text-sm text-green-900 whitespace-pre-wrap">{{ $submission->feedback }}</p>@endif
                @if($submission->hasMarkedFile())
                    <a href="{{ route('student.lms.submissions.marked', [$offering, $submission]) }}" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-semibold text-[#0f2744] shadow-sm ring-1 ring-green-200 hover:bg-green-100">
                        <i class="bi bi-file-earmark-pdf"></i> Download marked PDF
                    </a>
                @endif
            </div>
        @elseif($submission?->submitted_at)
            <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5 text-sm text-blue-900">Submitted on {{ $submission->submitted_at->format('d M Y H:i') }}. Awaiting grading.</div>
        @elseif($assignment->isOpenForSubmission())
            <form method="POST" action="{{ route('student.lms.assignments.submit', [$offering, $assignment]) }}" enctype="multipart/form-data" class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 space-y-4">
                @csrf
                <textarea name="body" rows="6" class="w-full rounded-xl border-gray-300 shadow-sm" placeholder="Written response (optional if uploading a file)"></textarea>
                <div>
                    <label class="text-sm font-medium text-gray-700">Upload file (PDF recommended)</label>
                    <input type="file" name="file" accept=".pdf,.doc,.docx" class="mt-1 w-full rounded-xl border-gray-300 text-sm">
                </div>
                <button type="submit" class="rounded-xl bg-[#8cc63f] px-5 py-2.5 font-semibold text-[#0f2744]">Submit assignment</button>
            </form>
        @else
            <div class="rounded-2xl border border-red-200 bg-red-50 p-5 text-sm text-red-800">This assignment is closed for submission.</div>
        @endif
    </div>
</x-app-layout>
