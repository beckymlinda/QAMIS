@php $logoSrc = \App\Support\ReportLogo::dataUri(); @endphp
<div style="margin-bottom: 20px; border-bottom: 2px solid #0f2744; padding-bottom: 12px;">
    @if($logoSrc)
        <img src="{{ $logoSrc }}" alt="Institution logo" style="height: 70px; max-width: 220px; object-fit: contain; display: block; margin-bottom: 10px;">
    @endif
    @if(! empty($title))
        <h1 style="font-size: 20px; color: #0f2744; margin: 0 0 6px;">{{ $title }}</h1>
    @endif
    @if(! empty($subtitle))
        <h2 style="font-size: 16px; color: #0f2744; margin: 0 0 4px; font-weight: 600;">{{ $subtitle }}</h2>
    @endif
    @if(! empty($meta))
        <p style="margin: 0; color: #666; font-size: 11px;">{{ $meta }}</p>
    @endif
</div>
