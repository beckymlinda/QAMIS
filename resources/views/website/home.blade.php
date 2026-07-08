@extends('website.layout', ['currentPage' => 'home', 'title' => $website->displayName().' — Home', 'preview' => $preview ?? false])

@section('content')
@php
    $features = $website->hero_features ?: [
        'Quality education and student-centred learning',
        'Experienced faculty and modern facilities',
        'Online applications and student portal access',
    ];
@endphp

@include('website.partials.hero-slider')

<section class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
    <div class="grid gap-6 md:grid-cols-3">
        @foreach($features as $feature)
            @if(filled($feature))
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 transition hover:shadow-md">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl text-lg font-bold text-white" style="background:var(--brand-secondary);color:var(--brand-primary)">
                        <i class="bi bi-check-lg"></i>
                    </span>
                    <p class="mt-4 text-sm font-medium leading-relaxed text-gray-700">{{ $feature }}</p>
                </div>
            @endif
        @endforeach
    </div>
</section>

@if($website->about_content)
    <section class="bg-white py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="grid items-center gap-10 lg:grid-cols-2">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider" style="color:var(--brand-secondary)">About us</p>
                    <h2 class="mt-2 text-3xl font-bold" style="color:var(--brand-primary)">Learn more about {{ $website->displayName() }}</h2>
                    <p class="mt-4 line-clamp-6 whitespace-pre-wrap text-gray-600 leading-relaxed">{{ Str::limit($website->about_content, 400) }}</p>
                    <a href="{{ route('school.about', $website->slug) }}" class="mt-6 inline-flex items-center gap-2 text-sm font-semibold transition hover:opacity-80" style="color:var(--brand-primary)">
                        Read full story <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="rounded-2xl p-8 text-white shadow-lg" style="background:linear-gradient(135deg, var(--brand-primary), color-mix(in srgb, var(--brand-primary) 70%, var(--brand-secondary)))">
                    <p class="text-lg font-semibold">Ready to join us?</p>
                    <p class="mt-2 text-sm text-white/85">Start your application online and track your admission status through our portal.</p>
                    <a href="{{ route('school.applications', $website->slug) }}" class="mt-6 inline-flex rounded-xl px-5 py-2.5 text-sm font-bold" style="background:var(--brand-secondary);color:var(--brand-primary)">
                        Start application
                    </a>
                </div>
            </div>
        </div>
    </section>
@endif

@php $featuredProgrammes = $website->institution->programmes->take(3); @endphp
@if($featuredProgrammes->isNotEmpty())
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider" style="color:var(--brand-secondary)">Academics</p>
                <h2 class="mt-1 text-3xl font-bold" style="color:var(--brand-primary)">Programmes offered</h2>
            </div>
            <a href="{{ route('school.programs', $website->slug) }}" class="hidden text-sm font-semibold sm:inline-flex" style="color:var(--brand-primary)">View all →</a>
        </div>
        <div class="mt-8 grid gap-6 md:grid-cols-3">
            @foreach($featuredProgrammes as $programme)
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 transition hover:shadow-md">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ ucfirst($programme->level) }}</p>
                    <h3 class="mt-2 text-lg font-bold text-gray-900">{{ $programme->name }}</h3>
                    @if($programme->orgUnit)
                        <p class="mt-1 text-sm text-gray-500">{{ $programme->orgUnit->name }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </section>
@endif
@endsection
