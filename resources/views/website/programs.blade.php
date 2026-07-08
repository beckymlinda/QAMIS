@extends('website.layout', ['currentPage' => 'programs', 'title' => $website->displayName().' — Programmes'])

@section('content')
<section class="text-white" style="background:var(--brand-primary)">
    <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
        <p class="text-sm font-semibold uppercase tracking-wider" style="color:var(--brand-secondary)">Academics</p>
        <h1 class="mt-2 text-4xl font-bold">Programmes offered</h1>
        @if($website->programs_intro)
            <p class="mt-4 max-w-3xl text-lg text-white/85">{{ $website->programs_intro }}</p>
        @endif
    </div>
</section>

<section class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
    @if($programmes->isEmpty())
        <div class="rounded-2xl bg-white p-12 text-center shadow-sm ring-1 ring-gray-100">
            <p class="text-gray-500">Programmes will appear here once added in the admin portal.</p>
        </div>
    @else
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach($programmes as $programme)
                <div class="flex flex-col rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 transition hover:shadow-md">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ ucfirst($programme->level) }}</p>
                    <h2 class="mt-2 text-xl font-bold text-gray-900">{{ $programme->name }}</h2>
                    @if($programme->orgUnit)
                        <p class="mt-1 flex items-center gap-1.5 text-sm text-gray-500">
                            <i class="bi bi-building"></i> {{ $programme->orgUnit->name }}
                        </p>
                    @endif
                    @if($programme->delivery_modes)
                        <p class="mt-3 text-sm text-gray-600">Delivery: {{ implode(', ', $programme->delivery_modes) }}</p>
                    @endif
                    <div class="mt-auto pt-6">
                        <a href="{{ route('school.applications', $website->slug) }}" class="inline-flex items-center gap-1 text-sm font-semibold" style="color:var(--brand-primary)">
                            Apply for this programme <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>
@endsection
