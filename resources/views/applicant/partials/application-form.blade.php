@php
    $nameParts = explode(' ', (string) ($user->name ?? ''), 2);
    $defaultFirst = old('first_name', $application?->first_name ?? $nameParts[0] ?? '');
    $defaultLast = old('last_name', $application?->last_name ?? $nameParts[1] ?? '');
    $isEdit = ! empty($application);
@endphp

<form
    method="POST"
    action="{{ $isEdit ? route('applicant.applications.update', $application) : route('applicant.apply.store', $website->slug) }}"
    enctype="multipart/form-data"
    class="space-y-8"
>
    @csrf
    @if($isEdit) @method('PUT') @endif

    {{-- Programme --}}
    <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
        <div class="flex items-start gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-primary/10 text-brand-primary"><i class="bi bi-mortarboard text-lg"></i></span>
            <div class="min-w-0 flex-1">
                <h3 class="text-lg font-bold text-gray-900">Programme selection</h3>
                <p class="mt-1 text-sm text-gray-500">Choose the programme you are applying for.</p>
                <select name="programme_id" required class="mt-4 w-full rounded-xl border-gray-300 text-sm">
                    <option value="">Select programme</option>
                    @foreach($programmes as $programme)
                        <option value="{{ $programme->id }}" @selected(old('programme_id', $application?->programme_id) == $programme->id)>
                            {{ $programme->name }} — App fee: {{ $programme->formattedFee($programme->application_fee) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    {{-- Personal details --}}
    <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
        <div class="flex items-start gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-primary/10 text-brand-primary"><i class="bi bi-person text-lg"></i></span>
            <div class="min-w-0 flex-1 space-y-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Personal details</h3>
                    <p class="mt-1 text-sm text-gray-500">Your contact and identity information.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-gray-700">First name *</label>
                        <input name="first_name" value="{{ $defaultFirst }}" required class="mt-1 w-full rounded-xl border-gray-300">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Last name *</label>
                        <input name="last_name" value="{{ $defaultLast }}" required class="mt-1 w-full rounded-xl border-gray-300">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Email *</label>
                        <input type="email" name="email" value="{{ old('email', $application?->email ?? $user->email ?? '') }}" required class="mt-1 w-full rounded-xl border-gray-300">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Phone</label>
                        <input name="phone" value="{{ old('phone', $application?->phone ?? '') }}" class="mt-1 w-full rounded-xl border-gray-300">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Date of birth</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $application?->date_of_birth?->format('Y-m-d') ?? '') }}" class="mt-1 w-full rounded-xl border-gray-300">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Gender</label>
                        <select name="gender" class="mt-1 w-full rounded-xl border-gray-300">
                            <option value="">—</option>
                            @foreach(['male','female','other'] as $g)
                                <option value="{{ $g }}" @selected(old('gender', $application?->gender ?? '') === $g)>{{ ucfirst($g) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-sm font-medium text-gray-700">Address</label>
                        <textarea name="address" rows="2" class="mt-1 w-full rounded-xl border-gray-300">{{ old('address', $application?->address ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Nationality</label>
                        <input name="nationality" value="{{ old('nationality', $application?->nationality ?? '') }}" class="mt-1 w-full rounded-xl border-gray-300">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Payment reference</label>
                        <input name="payment_reference" value="{{ old('payment_reference', $application?->payment_reference ?? '') }}" class="mt-1 w-full rounded-xl border-gray-300" placeholder="Bank / mobile money reference">
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Grades --}}
    <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
        @include('applicant.partials.certificate-grades', ['gradeData' => $gradeData])
    </section>

    {{-- Documents --}}
    <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
        @include('applicant.partials.document-upload', ['maxUploadMb' => $maxUploadMb, 'application' => $application ?? null])
    </section>

    <div class="flex flex-wrap items-center justify-between gap-4">
        @if($isEdit)
            <a href="{{ route('applicant.applications.show', $application) }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900">Cancel</a>
        @endif
        <button type="submit" class="ml-auto inline-flex items-center gap-2 rounded-xl px-8 py-3 text-sm font-bold text-white shadow-sm hover:opacity-90" style="background: var(--brand-primary, #0f2744);">
            <i class="bi bi-{{ $isEdit ? 'check-lg' : 'send' }}"></i>
            {{ $isEdit ? 'Save changes' : 'Submit application' }}
        </button>
    </div>
</form>
