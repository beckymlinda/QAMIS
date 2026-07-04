<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <a href="{{ route('student.evaluations') }}" class="text-sm text-[#0f2744] hover:text-[#8cc63f]">← Back to evaluations</a>
            <h2 class="font-semibold text-xl text-[#0f2744]">Evaluate: {{ $offering->course->code }}</h2>
            <p class="text-sm text-gray-500">{{ $offering->course->title }} · Lecturer: {{ $offering->lecturer?->name ?? 'TBA' }}</p>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('student.evaluations.submit', $offering) }}" class="space-y-8">
            @csrf

            <div class="rounded-lg bg-[#0f2744]/5 border border-[#0f2744]/10 p-4 text-sm text-gray-700">
                Rate each statement: <strong>1 = Strongly Disagree</strong> … <strong>5 = Strongly Agree</strong>.
                Open-ended responses are optional but encouraged.
            </div>

            @php
                $grouped = $questions->groupBy(fn ($q) => $q->category->section);
                $sectionLabels = ['course' => 'A. Course Evaluation', 'lecturer' => 'B. Lecturer Evaluation', 'open' => 'C. Open Comments'];
            @endphp

            @foreach($grouped as $section => $sectionQuestions)
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-[#0f2744] mb-4">{{ $sectionLabels[$section] ?? ucfirst($section) }}</h3>

                    @php $currentCategory = null; @endphp
                    @foreach($sectionQuestions as $question)
                        @if($currentCategory !== $question->category_id)
                            @php $currentCategory = $question->category_id; @endphp
                            <h4 class="font-medium text-[#0f2744] mt-4 mb-2 first:mt-0">{{ $question->category->title }}</h4>
                        @endif

                        <div class="mb-4 pb-4 border-b border-gray-100 last:border-0">
                            <p class="text-sm text-gray-800 mb-2">{{ $question->question_text }}</p>
                            @if($question->isLikert())
                                <div class="flex flex-wrap gap-3 text-sm">
                                    @for($i = 1; $i <= 5; $i++)
                                        <label class="inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 cursor-pointer hover:bg-gray-50 has-[:checked]:border-[#8cc63f] has-[:checked]:bg-[#8cc63f]/10">
                                            <input type="radio" name="responses[{{ $question->id }}]" value="{{ $i }}" required
                                                {{ (int) old("responses.{$question->id}", $existingResponses[$question->id] ?? 0) === $i ? 'checked' : '' }}
                                                class="text-[#8cc63f] focus:ring-[#8cc63f]">
                                            {{ $i }}
                                        </label>
                                    @endfor
                                </div>
                                @error("responses.{$question->id}")<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                            @else
                                <textarea name="responses[{{ $question->id }}]" rows="3" class="w-full rounded-md border-gray-300 text-sm">{{ old("responses.{$question->id}", $existingResponses[$question->id] ?? '') }}</textarea>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endforeach

            <div class="flex justify-end gap-3">
                <a href="{{ route('student.evaluations') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700">Cancel</a>
                <button type="submit" class="rounded-lg bg-[#0f2744] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#1a3a5c]" onclick="return confirm('Submit evaluation? You cannot change answers after submission.');">
                    Submit evaluation
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
