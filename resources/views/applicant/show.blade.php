<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-secondary">My application</p>
                <h2 class="text-xl font-bold text-brand-primary">{{ $application->application_number }}</h2>
                <p class="text-sm text-gray-500">{{ $application->programme->name }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if($application->canBeEditedByApplicant())
                    <a href="{{ route('applicant.applications.edit', $application) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-brand-secondary px-4 py-2 text-sm font-bold text-brand-primary">
                        <i class="bi bi-pencil"></i> Edit application
                    </a>
                @endif
                <a href="{{ route('applicant.dashboard') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Dashboard</a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6 px-4 py-8">
        @include('partials.alerts')

        {{-- Status hero --}}
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
            <div class="px-6 py-5 text-white" style="background: linear-gradient(135deg, var(--brand-primary, #0f2744), color-mix(in srgb, var(--brand-primary, #0f2744) 75%, #000));">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="rounded-full px-3 py-1 text-xs font-bold ring-1" style="background: rgba(255,255,255,0.2); color: #fff; border-color: rgba(255,255,255,0.35);">{{ $application->status->label() }}</span>
                    @if($application->isPaymentVerified())
                        <span class="text-xs" style="color: rgba(255,255,255,0.9);"><i class="bi bi-check-circle"></i> Payment verified</span>
                    @else
                        <span class="text-xs font-medium" style="color: #fde68a;"><i class="bi bi-clock"></i> Payment pending verification</span>
                    @endif
                </div>
                <p class="mt-3 text-sm" style="color: rgba(255,255,255,0.85);">Submitted {{ $application->submitted_at?->format('d M Y, H:i') ?? '—' }}</p>
            </div>
            @if($application->admin_notes)
                <div class="border-t border-gray-100 px-6 py-4">
                    <p class="text-xs font-semibold uppercase text-gray-500">Message from admissions</p>
                    <p class="mt-2 text-sm text-gray-700 whitespace-pre-line">{{ $application->admin_notes }}</p>
                </div>
            @endif
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                {{-- Personal --}}
                <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <h3 class="flex items-center gap-2 text-base font-bold text-gray-900"><i class="bi bi-person text-brand-primary"></i> Personal details</h3>
                    <dl class="mt-4 grid gap-4 sm:grid-cols-2 text-sm">
                        <div><dt class="text-gray-500">Full name</dt><dd class="mt-0.5 font-medium">{{ $application->fullName() }}</dd></div>
                        <div><dt class="text-gray-500">Email</dt><dd class="mt-0.5 font-medium">{{ $application->email }}</dd></div>
                        <div><dt class="text-gray-500">Phone</dt><dd class="mt-0.5 font-medium">{{ $application->phone ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Date of birth</dt><dd class="mt-0.5 font-medium">{{ $application->date_of_birth?->format('d M Y') ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Gender</dt><dd class="mt-0.5 font-medium">{{ ucfirst($application->gender ?? '—') }}</dd></div>
                        <div><dt class="text-gray-500">Nationality</dt><dd class="mt-0.5 font-medium">{{ $application->nationality ?? '—' }}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-gray-500">Address</dt><dd class="mt-0.5 font-medium whitespace-pre-line">{{ $application->address ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Payment reference</dt><dd class="mt-0.5 font-medium">{{ $application->payment_reference ?? '—' }}</dd></div>
                    </dl>
                </section>

                {{-- Grades --}}
                <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <h3 class="flex items-center gap-2 text-base font-bold text-gray-900"><i class="bi bi-table text-brand-primary"></i> Certificate grades</h3>
                    <div class="mt-4 overflow-hidden rounded-xl border border-gray-200">
                        @include('applications.partials.grades-table', ['application' => $application])
                    </div>
                </section>

                {{-- Documents --}}
                <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <h3 class="flex items-center gap-2 text-base font-bold text-gray-900"><i class="bi bi-folder2-open text-brand-primary"></i> Uploaded documents</h3>
                    <div class="mt-4 space-y-3">
                        @foreach($documentTypes as $field => $meta)
                            @php $path = $application->documentPaths()[$field] ?? null; @endphp
                            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-100 px-4 py-3 {{ $path ? 'bg-gray-50' : 'bg-white' }}">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $meta['label'] }}</p>
                                    <p class="text-xs {{ $path ? 'text-green-700' : 'text-gray-400' }}">{{ $path ? 'Uploaded' : 'Not uploaded' }}</p>
                                </div>
                                @if($path)
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('applicant.applications.file.preview', [$application, $field]) }}" target="_blank" class="rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-brand-primary ring-1 ring-gray-200">Preview</a>
                                        <a href="{{ route('applicant.applications.file', [$application, $field]) }}" class="rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 ring-1 ring-gray-200">Download</a>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>

            {{-- Timeline --}}
            <aside class="space-y-6">
                <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <h3 class="text-base font-bold text-gray-900">Application timeline</h3>
                    <ul class="mt-4 space-y-4">
                        @foreach($timeline as $step)
                            <li class="flex gap-3">
                                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $step['date'] ? 'bg-brand-secondary' : 'bg-gray-300' }}"></span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $step['label'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $step['date']?->format('d M Y, H:i') ?? 'Pending' }}</p>
                                    @if($step['note'])<p class="mt-1 text-xs text-gray-600">{{ $step['note'] }}</p>@endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>

                @if($application->canBeEditedByApplicant())
                    <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
                        The application window is still open. You can <a href="{{ route('applicant.applications.edit', $application) }}" class="font-semibold underline">edit your application</a> to update details or replace documents.
                    </div>
                @endif
            </aside>
        </div>
    </div>
</x-app-layout>
