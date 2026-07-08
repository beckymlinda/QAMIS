@php use Illuminate\Support\Facades\Storage; @endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-secondary">Settings</p>
                <h2 class="text-xl font-bold text-brand-primary">Website contents</h2>
                <p class="text-sm text-gray-500">Customize your public school website, branding, and application information.</p>
            </div>
            <div class="flex flex-col items-end gap-2">
                @if($settings->is_published)
                    <span class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white">
                        <i class="bi bi-check-circle-fill"></i> Published
                    </span>
                    <a href="{{ route('school.home', $settings->slug) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-blue-900 hover:bg-gray-50">
                        <i class="bi bi-box-arrow-up-right"></i> View live site
                    </a>
                    <form method="POST" action="{{ route('settings.website.toggle-publish', $institution) }}">
                        @csrf
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-red-300 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-100">
                            <i class="bi bi-eye-slash"></i> Unpublish
                        </button>
                    </form>
                @else
                    <span class="inline-flex items-center gap-2 rounded-xl bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700">
                        <i class="bi bi-eye-slash"></i> Unpublished
                    </span>
                    <a href="{{ route('settings.website.preview', $settings) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-brand-secondary px-4 py-2 text-sm font-bold text-brand-primary hover:opacity-90">
                        <i class="bi bi-eye"></i> Preview site
                    </a>
                    <form method="POST" action="{{ route('settings.website.toggle-publish', $institution) }}">
                        @csrf
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90" style="background:#2563eb">
                            <i class="bi bi-globe"></i> Publish
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @include('partials.alerts')

        @if($settings->is_published)
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                <i class="bi bi-globe"></i> Your public website is live at
                <a href="{{ route('school.home', $settings->slug) }}" target="_blank" class="font-semibold underline">{{ url('/school/'.$settings->slug) }}</a>
            </div>
        @endif

        <form method="POST" action="{{ route('settings.website.update', $institution) }}" enctype="multipart/form-data"
              x-data="{ tab: 'branding' }" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="flex flex-wrap gap-2 border-b border-gray-200 pb-4">
                @foreach(['branding' => 'Branding', 'homepage' => 'Homepage', 'about' => 'About', 'programs' => 'Programs', 'applications' => 'Applications', 'footer' => 'Footer'] as $key => $label)
                    <button type="button" @click="tab = '{{ $key }}'"
                            class="rounded-lg px-4 py-2 text-sm font-semibold transition"
                            :class="tab === '{{ $key }}' ? 'ring-2 ring-brand-primary bg-brand-primary/10 text-brand-primary' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            {{-- General / publish --}}
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-gray-700">School display name</label>
                        <input type="text" name="school_name" value="{{ old('school_name', $settings->school_name) }}" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Website URL slug</label>
                        <div class="mt-1 flex rounded-xl ring-1 ring-gray-300">
                            <span class="flex items-center rounded-l-xl bg-gray-50 px-3 text-sm text-gray-500">/school/</span>
                            <input type="text" name="slug" value="{{ old('slug', $settings->slug) }}" required class="w-full rounded-r-xl border-0 focus:ring-0">
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-xs text-gray-500">Use <strong>Publish</strong> or <strong>Unpublish</strong> above to control whether your site is visible at the URL below. Saving this form does not change publish status.</p>
                    </div>
                </div>
            </div>

            {{-- Branding tab --}}
            <div x-show="tab === 'branding'" class="space-y-6">
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 space-y-5">
                    <h3 class="font-bold text-brand-primary">Logo &amp; colors</h3>
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-gray-700">Primary color</label>
                            <div class="mt-1 flex items-center gap-3">
                                <input type="color" name="primary_color" value="{{ old('primary_color', $settings->primary_color) }}" class="h-11 w-16 cursor-pointer rounded-lg border-gray-300">
                                <input type="text" value="{{ old('primary_color', $settings->primary_color) }}" class="w-full rounded-xl border-gray-300 font-mono text-sm shadow-sm" readonly>
                            </div>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700">Secondary color</label>
                            <div class="mt-1 flex items-center gap-3">
                                <input type="color" name="secondary_color" value="{{ old('secondary_color', $settings->secondary_color) }}" class="h-11 w-16 cursor-pointer rounded-lg border-gray-300">
                                <input type="text" value="{{ old('secondary_color', $settings->secondary_color) }}" class="w-full rounded-xl border-gray-300 font-mono text-sm shadow-sm" readonly>
                            </div>
                        </div>
                    </div>
                    <div>
                        <x-image-upload-preview
                            name="logo"
                            accept="image/jpeg,image/png,image/webp,image/gif"
                            :existing-url="$settings->hasLogo() ? $settings->logoUrl() : null"
                            preview-class="h-14 w-28 shrink-0 rounded-lg object-contain bg-white p-1 ring-1 ring-gray-200"
                            label="School logo"
                        />
                        @if($settings->hasLogo())
                            <a href="{{ route('settings.website.logo.destroy', $institution) }}" onclick="event.preventDefault(); document.getElementById('remove-logo-form').submit();" class="mt-2 inline-block text-sm font-semibold text-red-600 hover:text-red-800">Remove saved logo</a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Homepage tab --}}
            <div x-show="tab === 'homepage'" class="space-y-6">
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 space-y-4">
                    <h3 class="font-bold text-brand-primary">Homepage content</h3>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Tagline</label>
                        <input type="text" name="tagline" value="{{ old('tagline', $settings->tagline) }}" placeholder="Excellence in Education" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Hero description</label>
                        <textarea name="hero_description" rows="4" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm">{{ old('hero_description', $settings->hero_description) }}</textarea>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Homepage highlights (up to 3)</label>
                        @for($i = 0; $i < 3; $i++)
                            <input type="text" name="hero_features[]" value="{{ old('hero_features.'.$i, ($settings->hero_features ?? [])[$i] ?? '') }}" class="mt-2 w-full rounded-xl border-gray-300 text-sm shadow-sm" placeholder="Highlight {{ $i + 1 }}">
                        @endfor
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Slider images (up to 6)</label>
                        @if($settings->slider_images)
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach($settings->slider_images as $index => $path)
                                    @if(Storage::disk('public')->exists($path))
                                        <label class="relative block shrink-0 overflow-hidden rounded-lg ring-1 ring-gray-200">
                                            <img src="{{ Storage::disk('public')->url($path) }}" alt="Slide {{ $index + 1 }}" class="h-16 w-28 object-cover">
                                            <span class="absolute inset-x-0 bottom-0 bg-black/70 px-1.5 py-0.5 text-[10px] text-white">
                                                #{{ $index + 1 }} <input type="checkbox" name="remove_slider[]" value="{{ $index }}" class="ml-1 rounded"> rm
                                            </span>
                                        </label>
                                    @endif
                                @endforeach
                            </div>
                            <p class="mt-2 text-xs text-gray-500">{{ count($settings->slider_images) }} saved · upload below to add more (max 6)</p>
                        @endif
                        <div class="mt-3">
                            <x-image-upload-preview
                                name="slider_images[]"
                                accept="image/jpeg,image/png,image/webp,image/gif"
                                :multiple="true"
                                preview-class="h-16 w-28 shrink-0 rounded-lg object-cover ring-1 ring-gray-200 bg-gray-50"
                            />
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Select multiple images at once. Recommended: 1600×900 px or wider.</p>
                    </div>
                </div>
            </div>

            {{-- About tab --}}
            <div x-show="tab === 'about'" class="space-y-6">
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <h3 class="font-bold text-brand-primary">About page</h3>
                    <textarea name="about_content" rows="10" class="mt-4 w-full rounded-xl border-gray-300 shadow-sm" placeholder="Tell visitors about your institution, mission, vision, and history…">{{ old('about_content', $settings->about_content) }}</textarea>
                </div>

                @php
                    $teamMembers = old('team_members', $settings->team_members ?? []);
                    if (empty($teamMembers)) {
                        $teamMembers = [['name' => '', 'role' => '', 'photo' => null]];
                    }
                @endphp
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100" x-data="{ teamCount: {{ count($teamMembers) }} }">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="font-bold text-brand-primary">Team <span class="font-normal text-gray-500">(optional)</span></h3>
                            <p class="mt-1 text-sm text-gray-500">Show leadership or key staff on the About page.</p>
                        </div>
                        <button type="button" @click="teamCount++" class="inline-flex items-center gap-1.5 rounded-xl bg-gray-100 px-3 py-2 text-sm font-semibold text-brand-primary hover:bg-gray-200">
                            <i class="bi bi-plus-lg"></i> Add another team member
                        </button>
                    </div>

                    <div class="mt-6 space-y-4">
                        @foreach($teamMembers as $index => $member)
                            <div class="rounded-xl border border-gray-200 p-4" x-show="{{ $index }} < teamCount" x-cloak>
                                <div class="flex items-start justify-between gap-3">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Team member {{ $index + 1 }}</p>
                                    <label class="inline-flex items-center gap-1 text-xs text-red-600">
                                        <input type="checkbox" name="remove_team_members[]" value="{{ $index }}" class="rounded"> Remove
                                    </label>
                                </div>
                                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Name</label>
                                        <input type="text" name="team_members[{{ $index }}][name]" value="{{ $member['name'] ?? '' }}" class="mt-1 w-full rounded-xl border-gray-300 text-sm shadow-sm">
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Role</label>
                                        <input type="text" name="team_members[{{ $index }}][role]" value="{{ $member['role'] ?? '' }}" class="mt-1 w-full rounded-xl border-gray-300 text-sm shadow-sm" placeholder="e.g. Principal, Dean">
                                    </div>
                                </div>
                                <div class="mt-4">
                                    @php
                                        $teamPhotoUrl = (!empty($member['photo']) && Storage::disk('public')->exists($member['photo']))
                                            ? Storage::disk('public')->url($member['photo'])
                                            : null;
                                    @endphp
                                    <x-image-upload-preview
                                        name="team_members[{{ $index }}][photo]"
                                        accept="image/jpeg,image/png,image/webp"
                                        :existing-url="$teamPhotoUrl"
                                        preview-class="h-16 w-16 shrink-0 rounded-full object-cover ring-2 ring-gray-200"
                                        label="Photo"
                                    />
                                    @if($teamPhotoUrl)
                                        <input type="hidden" name="team_members[{{ $index }}][existing_photo]" value="{{ $member['photo'] }}">
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        {{-- Extra empty slots for dynamically added members --}}
                        @for($i = count($teamMembers); $i < 12; $i++)
                            <div class="rounded-xl border border-dashed border-gray-200 p-4" x-show="teamCount > {{ $i }}" x-cloak>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Team member {{ $i + 1 }}</p>
                                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Name</label>
                                        <input type="text" name="team_members[{{ $i }}][name]" class="mt-1 w-full rounded-xl border-gray-300 text-sm shadow-sm">
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Role</label>
                                        <input type="text" name="team_members[{{ $i }}][role]" class="mt-1 w-full rounded-xl border-gray-300 text-sm shadow-sm">
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <x-image-upload-preview
                                        name="team_members[{{ $i }}][photo]"
                                        accept="image/jpeg,image/png,image/webp"
                                        preview-class="h-16 w-16 shrink-0 rounded-full object-cover ring-2 ring-gray-200"
                                        label="Photo"
                                    />
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>

            {{-- Programs tab --}}
            <div x-show="tab === 'programs'" class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h3 class="font-bold text-brand-primary">Programs page intro</h3>
                <p class="mt-1 text-sm text-gray-500">Programmes are pulled from your institution's programme list. Add introductory text here.</p>
                <textarea name="programs_intro" rows="5" class="mt-4 w-full rounded-xl border-gray-300 shadow-sm">{{ old('programs_intro', $settings->programs_intro) }}</textarea>
            </div>

            {{-- Applications tab --}}
            <div x-show="tab === 'applications'" class="space-y-6">
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 space-y-4">
                    <h3 class="font-bold text-brand-primary">Application page</h3>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Introduction</label>
                        <textarea name="application_intro" rows="3" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm">{{ old('application_intro', $settings->application_intro) }}</textarea>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Payment instructions</label>
                        <textarea name="application_payment_instructions" rows="8" class="mt-1 w-full rounded-xl border-gray-300 font-mono text-sm shadow-sm" placeholder="Bank name, account number, mobile money, reference instructions…">{{ old('application_payment_instructions', $settings->application_payment_instructions) }}</textarea>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Required uploads &amp; documents</label>
                        <textarea name="application_requirements" rows="8" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm">{{ old('application_requirements', $settings->application_requirements) }}</textarea>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Max upload size (MB)</label>
                        <input type="number" name="application_upload_max_mb" min="1" max="50" value="{{ old('application_upload_max_mb', $settings->application_upload_max_mb) }}" class="mt-1 w-32 rounded-xl border-gray-300 shadow-sm">
                    </div>
                </div>
            </div>

            {{-- Footer tab --}}
            <div x-show="tab === 'footer'" class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 space-y-4">
                <h3 class="font-bold text-brand-primary">Footer &amp; contact</h3>
                <div>
                    <label class="text-sm font-medium text-gray-700">Physical address</label>
                    <input type="text" name="footer_address" value="{{ old('footer_address', $settings->footer_address) }}" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm">
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Phone</label>
                        <input type="text" name="footer_phone" value="{{ old('footer_phone', $settings->footer_phone) }}" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="footer_email" value="{{ old('footer_email', $settings->footer_email) }}" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm">
                    </div>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Additional footer text</label>
                    <textarea name="footer_extra" rows="3" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm">{{ old('footer_extra', $settings->footer_extra) }}</textarea>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="rounded-xl bg-brand-secondary px-6 py-3 text-sm font-bold text-brand-primary shadow-sm hover:opacity-90">
                    Save website settings
                </button>
            </div>
        </form>

        <form id="remove-logo-form" method="POST" action="{{ route('settings.website.logo.destroy', $institution) }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</x-app-layout>
