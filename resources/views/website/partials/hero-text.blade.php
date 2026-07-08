@if($website->tagline)
    <p class="mb-3 inline-block rounded-full px-4 py-1 text-sm font-medium" style="background:color-mix(in srgb, var(--brand-secondary) 25%, transparent);color:var(--brand-secondary)">
        {{ $website->tagline }}
    </p>
@endif
<h1 class="max-w-3xl text-4xl font-bold leading-tight lg:text-5xl">{{ $website->displayName() }}</h1>
<p class="mt-6 max-w-2xl text-lg text-white/90 leading-relaxed">
    {{ $website->hero_description ?: 'Welcome to our institution. Explore our programmes and apply online today.' }}
</p>
<div class="mt-8 flex flex-wrap gap-4">
    <a href="{{ route('school.applications', $website->slug) }}" class="rounded-xl px-8 py-3 text-base font-bold shadow-lg transition hover:opacity-90" style="background:var(--brand-secondary);color:var(--brand-primary)">
        Apply for admission
    </a>
    <a href="{{ route('school.programs', $website->slug) }}" class="rounded-xl border border-white/40 px-8 py-3 text-base font-semibold transition hover:bg-white/10">
        View programmes
    </a>
</div>
