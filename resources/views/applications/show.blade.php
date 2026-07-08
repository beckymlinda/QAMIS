<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-brand-primary">{{ $application->application_number }}</h2>
                <p class="text-sm text-gray-500">{{ $application->fullName() }} · {{ $application->programme->name }}</p>
            </div>
            <a href="{{ route('applications.index') }}" class="text-sm font-medium text-brand-primary">← All applications</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6 px-4 py-8">
        @include('partials.alerts')

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $application->status->badgeClasses() }}">{{ $application->status->label() }}</span>
                    <dl class="mt-4 grid gap-4 sm:grid-cols-2 text-sm">
                        <div><dt class="text-gray-500">Email</dt><dd class="mt-0.5 font-medium">{{ $application->email }}</dd></div>
                        <div><dt class="text-gray-500">Phone</dt><dd class="mt-0.5 font-medium">{{ $application->phone ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">DOB</dt><dd class="mt-0.5 font-medium">{{ $application->date_of_birth?->format('d M Y') ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Gender</dt><dd class="mt-0.5 font-medium">{{ ucfirst($application->gender ?? '—') }}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-gray-500">Address</dt><dd class="mt-0.5 font-medium whitespace-pre-line">{{ $application->address ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Nationality</dt><dd class="mt-0.5 font-medium">{{ $application->nationality ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Payment reference</dt><dd class="mt-0.5 font-medium">{{ $application->payment_reference ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Submitted</dt><dd class="mt-0.5 font-medium">{{ $application->submitted_at?->format('d M Y, H:i') }}</dd></div>
                    </dl>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <h3 class="font-bold text-brand-primary">Certificate grades</h3>
                    <div class="mt-4 overflow-hidden rounded-xl border border-gray-200">
                        @include('applications.partials.grades-table', ['application' => $application])
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <h3 class="font-bold text-brand-primary">Documents</h3>
                    <div class="mt-4 space-y-3">
                        @foreach(\App\Support\ApplicationDocuments::types() as $field => $meta)
                            @php $path = $application->documentPaths()[$field] ?? null; @endphp
                            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-100 px-4 py-3 {{ $path ? 'bg-gray-50' : '' }}">
                                <div>
                                    <p class="text-sm font-semibold">{{ $meta['label'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $path ? 'Uploaded' : 'Not provided' }}</p>
                                </div>
                                @if($path)
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('applications.file.preview', [$application, $field]) }}" target="_blank" class="rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-brand-primary ring-1 ring-gray-200">Preview</a>
                                        <a href="{{ route('applications.file', [$application, $field]) }}" class="rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 ring-1 ring-gray-200">Download</a>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 space-y-4">
                    <h3 class="font-bold text-brand-primary">Update status</h3>
                    <p class="text-xs text-gray-500">Setting status to <strong>Enrolled</strong> creates the student record, adds them to Student Management, and activates the student portal.</p>
                    <form method="POST" action="{{ route('applications.status', $application) }}" class="space-y-3">
                        @csrf @method('PUT')
                        <select name="status" class="w-full rounded-xl border-gray-300 text-sm">@foreach($statuses as $val => $label)<option value="{{ $val }}" @selected($application->status->value===$val)>{{ $label }}</option>@endforeach</select>
                        <div>
                            <label class="text-xs font-medium text-gray-600">Year of study (when enrolling)</label>
                            <input type="number" name="year_of_study" value="1" min="1" max="10" class="mt-1 w-full rounded-xl border-gray-300 text-sm">
                        </div>
                        <textarea name="admin_notes" rows="4" placeholder="Comments to applicant" class="w-full rounded-xl border-gray-300 text-sm">{{ old('admin_notes', $application->admin_notes) }}</textarea>
                        <button class="w-full rounded-xl py-2.5 text-sm font-semibold text-white" style="background:#2563eb">Save status</button>
                    </form>
                </div>
                @if(!$application->isPaymentVerified() && $application->payment_proof_path)
                    <form method="POST" action="{{ route('applications.verify-payment', $application) }}">@csrf
                        <button class="w-full rounded-xl border border-green-300 bg-green-50 py-2.5 text-sm font-semibold text-green-800">Verify payment</button>
                    </form>
                @endif
                @if($application->canBeEnrolled())
                    <form method="POST" action="{{ route('applications.enroll', $application) }}" class="space-y-2">@csrf
                        <input type="number" name="year_of_study" value="1" min="1" max="10" class="w-full rounded-xl border-gray-300 text-sm" placeholder="Year of study">
                        <button class="w-full rounded-xl bg-brand-secondary py-2.5 text-sm font-bold text-brand-primary">Enroll student</button>
                    </form>
                @endif
                @if($application->enrolledStudent)
                    <a href="{{ route('students.show', $application->enrolledStudent) }}" class="block rounded-xl bg-gray-100 py-2.5 text-center text-sm font-semibold">View enrolled student</a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
