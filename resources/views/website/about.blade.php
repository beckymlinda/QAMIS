@extends('website.layout', ['currentPage' => 'about', 'title' => $website->displayName().' — About'])

@section('content')
<section class="text-white" style="background:var(--brand-primary)">
    <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
        <p class="text-sm font-semibold uppercase tracking-wider" style="color:var(--brand-secondary)">About us</p>
        <h1 class="mt-2 text-4xl font-bold">About {{ $website->displayName() }}</h1>
    </div>
</section>

<section class="mx-auto max-w-4xl px-4 py-16 sm:px-6">
    <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-gray-100">
        <div class="text-gray-700 leading-normal whitespace-pre-line">{{ $website->displayText($website->about_content) ?: 'Our institution is committed to quality higher education. Configure this page in Settings → Website Contents.' }}</div>
    </div>
</section>

@php $team = $website->teamMembersForDisplay(); @endphp
@if(count($team) > 0)
    <section class="bg-white py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <p class="text-sm font-semibold uppercase tracking-wider" style="color:var(--brand-secondary)">Our team</p>
            <h2 class="mt-1 text-3xl font-bold" style="color:var(--brand-primary)">Meet the people behind {{ $website->displayName() }}</h2>
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($team as $member)
                    <div class="overflow-hidden rounded-2xl bg-gray-50 ring-1 ring-gray-100 transition hover:shadow-md">
                        <div class="h-44 w-full overflow-hidden bg-gray-200">
                            @if($member['photo_url'])
                                <img src="{{ $member['photo_url'] }}" alt="{{ $member['name'] }}" class="h-full w-full object-cover object-center">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-4xl font-bold text-white" style="background:var(--brand-primary)">
                                    {{ strtoupper(substr($member['name'] ?: '?', 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div class="px-4 py-3 text-center">
                            <p class="truncate text-sm font-semibold text-gray-900">
                                @if($member['name'])
                                    <span>{{ $member['name'] }}</span>
                                @endif
                                @if($member['name'] && $member['role'])
                                    <span class="mx-1.5 font-normal text-gray-400">·</span>
                                @endif
                                @if($member['role'])
                                    <span style="color:var(--brand-primary)">{{ $member['role'] }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
@endsection
