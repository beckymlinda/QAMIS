@php $sliders = $website->sliderUrls(); @endphp

<section class="relative overflow-hidden text-white" style="background:var(--brand-primary)">
    @if(count($sliders) > 0)
        <div
            x-data="{
                active: 0,
                total: {{ count($sliders) }},
                timer: null,
                start() {
                    if (this.total <= 1) return;
                    this.timer = setInterval(() => { this.active = (this.active + 1) % this.total; }, 6000);
                },
                stop() { if (this.timer) clearInterval(this.timer); },
                go(index) { this.active = index; this.stop(); this.start(); },
                prev() { this.go((this.active - 1 + this.total) % this.total); },
                next() { this.go((this.active + 1) % this.total); },
            }"
            x-init="start()"
            @mouseenter="stop()"
            @mouseleave="start()"
            class="relative"
        >
            {{-- Fixed-height slide stage --}}
            <div class="relative h-[min(520px,55vh)] min-h-[420px] w-full">
                @foreach($sliders as $index => $url)
                    <div
                        x-show="active === {{ $index }}"
                        x-transition:enter="transition ease-out duration-700"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-500"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="absolute inset-0"
                        @if($index > 0) x-cloak @endif
                    >
                        <img
                            src="{{ $url }}"
                            alt="Slide {{ $index + 1 }}"
                            class="h-full w-full object-cover object-center"
                        >
                        <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/40 to-black/30"></div>
                    </div>
                @endforeach

                {{-- Hero text overlay --}}
                <div class="relative z-10 flex h-full items-center">
                    <div class="mx-auto w-full max-w-6xl px-4 py-12 sm:px-6">
                        @include('website.partials.hero-text')
                    </div>
                </div>

                @if(count($sliders) > 1)
                    <button type="button" @click="prev()" class="absolute left-3 top-1/2 z-20 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-black/30 text-white backdrop-blur-sm transition hover:bg-black/50 sm:left-6" aria-label="Previous slide">
                        <i class="bi bi-chevron-left text-xl"></i>
                    </button>
                    <button type="button" @click="next()" class="absolute right-3 top-1/2 z-20 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-black/30 text-white backdrop-blur-sm transition hover:bg-black/50 sm:right-6" aria-label="Next slide">
                        <i class="bi bi-chevron-right text-xl"></i>
                    </button>

                    <div class="absolute bottom-5 left-1/2 z-20 flex -translate-x-1/2 items-center gap-2">
                        @foreach($sliders as $index => $url)
                            <button
                                type="button"
                                @click="go({{ $index }})"
                                class="h-2.5 rounded-full transition-all duration-300"
                                :class="active === {{ $index }} ? 'w-8 bg-white' : 'w-2.5 bg-white/50 hover:bg-white/70'"
                                aria-label="Go to slide {{ $index + 1 }}"
                            ></button>
                        @endforeach
                    </div>

                    <p class="absolute bottom-5 right-4 z-20 hidden rounded-full bg-black/30 px-3 py-1 text-xs font-medium text-white/90 backdrop-blur-sm sm:block">
                        <span x-text="active + 1"></span> / {{ count($sliders) }}
                    </p>
                @endif
            </div>
        </div>
    @else
        <div class="mx-auto max-w-6xl px-4 py-20 sm:px-6 lg:py-28">
            @include('website.partials.hero-text')
        </div>
    @endif
</section>
