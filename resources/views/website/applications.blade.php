@extends('website.layout', ['currentPage' => 'applications', 'title' => $website->displayName().' — Apply'])

@section('content')
@php
    $requirements = $website->displayText($website->application_requirements) ?: $website->defaultApplicationRequirements();
    $paymentInfo = $website->displayText($website->application_payment_instructions) ?: $website->defaultApplicationPaymentInstructions();
    $intro = $website->displayText($website->application_intro) ?: 'Apply to join our institution. Select a programme, complete the form, upload required documents, and pay the application fee.';
@endphp

<section class="text-white" style="background:var(--brand-primary)">
    <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
        <p class="text-sm font-semibold uppercase tracking-wider" style="color:var(--brand-secondary)">Admissions</p>
        <h1 class="mt-2 text-4xl font-bold">Online application</h1>
        <p class="mt-4 max-w-3xl text-lg text-white/85 whitespace-pre-line">{{ $intro }}</p>
    </div>
</section>

<section class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
    <div class="grid gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h2 class="flex items-center gap-2 text-lg font-bold" style="color:var(--brand-primary)">
                    <i class="bi bi-mortarboard"></i> Select a programme
                </h2>
                @if($programmes->isEmpty())
                    <p class="mt-4 text-sm text-gray-500">No programmes are available for application yet.</p>
                @else
                    <div class="mt-4 space-y-3">
                        @foreach($programmes as $programme)
                            <div class="rounded-xl border border-gray-100 p-4 transition hover:border-gray-200 hover:shadow-sm">
                                <p class="font-semibold text-gray-900">{{ $programme->name }}</p>
                                <p class="mt-1 text-sm text-gray-500">{{ ucfirst($programme->level) }}@if($programme->orgUnit) · {{ $programme->orgUnit->name }}@endif</p>
                                @if($programme->duration)<p class="mt-1 text-xs text-gray-500">Duration: {{ $programme->duration }}</p>@endif
                                @if($programme->total_credit_hours)<p class="text-xs text-gray-500">Credit hours: {{ $programme->total_credit_hours }}</p>@endif
                                <p class="mt-2 text-xs font-medium" style="color:var(--brand-primary)">
                                    Application fee: {{ $programme->formattedFee($programme->application_fee) }}
                                    · Tuition: {{ $programme->formattedFee($programme->tuition_fee) }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h2 class="flex items-center gap-2 text-lg font-bold" style="color:var(--brand-primary)">
                    <i class="bi bi-cloud-upload"></i> Required documents
                </h2>
                <div class="mt-4 text-sm text-gray-700 whitespace-pre-line">{{ $requirements }}</div>
                <p class="mt-3 text-xs text-gray-500">Maximum upload size: {{ $website->application_upload_max_mb }} MB per file</p>
            </div>

            <div class="rounded-2xl border-2 border-dashed p-8 text-center" style="border-color:color-mix(in srgb, var(--brand-secondary) 50%, #e5e7eb)">
                <i class="bi bi-person-plus text-4xl" style="color:var(--brand-primary)"></i>
                <p class="mt-4 font-semibold text-gray-900">Ready to apply?</p>
                <p class="mt-2 text-sm text-gray-600">Create a dedicated applicant account for {{ $website->displayName() }} — separate from staff or institution login.</p>
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('school.apply.register', $website->slug) }}" class="rounded-xl px-6 py-2.5 text-sm font-bold text-white" style="background:var(--brand-primary)">Create applicant account</a>
                    <a href="{{ route('school.apply.login', $website->slug) }}" class="rounded-xl border border-gray-300 px-6 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Applicant login</a>
                </div>
                @auth
                    @if(auth()->user()->isApplicant() && auth()->user()->institution_id === $website->institution_id)
                        <a href="{{ route('applicant.apply.create', $website->slug) }}" class="mt-4 inline-flex rounded-xl px-6 py-2.5 text-sm font-bold" style="background:var(--brand-secondary);color:var(--brand-primary)">Continue your application</a>
                    @endif
                @endauth
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl p-6 text-white shadow-lg" style="background:var(--brand-primary)">
                <h2 class="flex items-center gap-2 text-lg font-bold">
                    <i class="bi bi-credit-card"></i> Payment information
                </h2>
                <div class="mt-4 text-sm text-white/90 whitespace-pre-line">{{ $paymentInfo }}</div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h2 class="text-sm font-bold uppercase tracking-wide text-gray-500">Application process</h2>
                <ol class="mt-4 space-y-3 text-sm text-gray-700">
                    <li class="flex gap-3"><span class="font-bold" style="color:var(--brand-secondary)">1.</span> Select your programme</li>
                    <li class="flex gap-3"><span class="font-bold" style="color:var(--brand-secondary)">2.</span> Complete the application form</li>
                    <li class="flex gap-3"><span class="font-bold" style="color:var(--brand-secondary)">3.</span> Upload required documents</li>
                    <li class="flex gap-3"><span class="font-bold" style="color:var(--brand-secondary)">4.</span> Pay application fee & upload proof</li>
                    <li class="flex gap-3"><span class="font-bold" style="color:var(--brand-secondary)">5.</span> Track status in your portal</li>
                </ol>
            </div>
        </div>
    </div>
</section>
@endsection
