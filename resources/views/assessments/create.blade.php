<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">New {{ ucfirst($type) }} Assessment</h2></x-slot>
    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('assessments.store') }}" class="bg-white p-6 rounded-lg shadow space-y-4">
            @csrf
            <input type="hidden" name="assessment_type" value="{{ $type }}">
            <div><label class="block text-sm font-medium">Template</label>
                <select name="assessment_template_id" class="mt-1 w-full rounded-md border-gray-300" required>
                    @foreach ($templates->where('type', $type) as $template)
                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                    @endforeach
                </select>
            </div>
            @if($type === 'programme')
                <div><label class="block text-sm font-medium">Programme</label>
                    <select name="programme_id" class="mt-1 w-full rounded-md border-gray-300" required>
                        @foreach ($programmes as $programme)
                            <option value="{{ $programme->id }}">{{ $programme->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div><label class="block text-sm font-medium">Title</label><input name="title" class="mt-1 w-full rounded-md border-gray-300" required value="{{ old('title', ucfirst($type).' Assessment '.date('Y')) }}"></div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium">Period Start</label><input type="date" name="period_start" class="mt-1 w-full rounded-md border-gray-300"></div>
                <div><label class="block text-sm font-medium">Period End</label><input type="date" name="period_end" class="mt-1 w-full rounded-md border-gray-300"></div>
            </div>
            <div><label class="block text-sm font-medium">Assessor Names</label><input name="assessor_names" class="mt-1 w-full rounded-md border-gray-300"></div>
            <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">Create Assessment</button>
        </form>
    </div>
</x-app-layout>
