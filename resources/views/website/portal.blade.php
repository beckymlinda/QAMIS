@extends('website.layout', ['currentPage' => 'portal', 'title' => $website->displayName().' — Student Portal'])

@section('content')
<section class="text-white" style="background:var(--brand-primary)">
    <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6">
        <p class="text-sm font-semibold uppercase tracking-wider" style="color:var(--brand-secondary)">Student portal</p>
        <h1 class="mt-2 text-4xl font-bold">Access your account</h1>
        <p class="mt-4 max-w-2xl text-lg text-white/85">
            Apply for admission, track your application, or sign in as an enrolled student to access courses, timetable, and results.
        </p>
    </div>
</section>

<section class="mx-auto max-w-6xl px-4 py-12 sm:px-6">
    @auth
        @if(auth()->user()->isApplicant() && auth()->user()->institution_id === $website->institution_id)
            <div class="mb-8 rounded-2xl border border-blue-200 bg-blue-50 p-6">
                <p class="font-semibold text-blue-900">You are signed in as an applicant</p>
                <p class="mt-1 text-sm text-blue-800">Continue to your application dashboard to track status or edit your submission.</p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <a href="{{ route('applicant.dashboard') }}" class="rounded-xl px-5 py-2.5 text-sm font-bold text-white" style="background:var(--brand-primary)">My applications</a>
                    <a href="{{ route('applicant.apply.create', $website->slug) }}" class="rounded-xl px-5 py-2.5 text-sm font-bold" style="background:var(--brand-secondary);color:var(--brand-primary)">Start / continue application</a>
                </div>
            </div>
        @elseif(auth()->user()->hasRole('student') && auth()->user()->institution_id === $website->institution_id)
            <div class="mb-8 rounded-2xl border border-green-200 bg-green-50 p-6">
                <p class="font-semibold text-green-900">You are signed in as a student</p>
                <p class="mt-1 text-sm text-green-800">Go to your student portal for courses, timetable, and exam results.</p>
                <a href="{{ route('student.dashboard') }}" class="mt-4 inline-flex rounded-xl px-5 py-2.5 text-sm font-bold text-white" style="background:var(--brand-primary)">Open student portal</a>
            </div>
        @endif
    @endauth

    <div class="grid gap-8 lg:grid-cols-2">
        {{-- Applicants --}}
        <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-gray-100">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl text-xl text-white" style="background:var(--brand-primary)">
                <i class="bi bi-file-earmark-person"></i>
            </div>
            <h2 class="mt-4 text-xl font-bold text-gray-900">Applying for admission</h2>
            <p class="mt-2 text-sm text-gray-600">
                Create an applicant account to submit your application online, upload documents, and track your admission status at {{ $website->displayName() }}.
            </p>
            <ul class="mt-4 space-y-2 text-sm text-gray-700">
                <li class="flex gap-2"><i class="bi bi-check2" style="color:var(--brand-secondary)"></i> Create a dedicated applicant account</li>
                <li class="flex gap-2"><i class="bi bi-check2" style="color:var(--brand-secondary)"></i> Submit and edit your application</li>
                <li class="flex gap-2"><i class="bi bi-check2" style="color:var(--brand-secondary)"></i> Track review and enrollment status</li>
            </ul>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('school.apply.register', $website->slug) }}" class="rounded-xl px-5 py-2.5 text-sm font-bold text-white" style="background:var(--brand-primary)">Create applicant account</a>
                <a href="{{ route('school.apply.login', $website->slug) }}" class="rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Applicant login</a>
            </div>
            <a href="{{ route('school.applications', $website->slug) }}" class="mt-4 inline-flex text-sm font-semibold hover:opacity-80" style="color:var(--brand-primary)">View application requirements →</a>
        </div>

        {{-- Enrolled students --}}
        <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-gray-100">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl text-xl" style="background:var(--brand-secondary);color:var(--brand-primary)">
                <i class="bi bi-mortarboard"></i>
            </div>
            <h2 class="mt-4 text-xl font-bold text-gray-900">Enrolled students</h2>
            <p class="mt-2 text-sm text-gray-600">
                Already enrolled? Sign in to access your student portal — courses, timetable, exam results, and lecturer evaluations.
            </p>
            <ul class="mt-4 space-y-2 text-sm text-gray-700">
                <li class="flex gap-2"><i class="bi bi-check2" style="color:var(--brand-secondary)"></i> My courses &amp; LMS</li>
                <li class="flex gap-2"><i class="bi bi-check2" style="color:var(--brand-secondary)"></i> Timetable &amp; exam results</li>
                <li class="flex gap-2"><i class="bi bi-check2" style="color:var(--brand-secondary)"></i> Teaching evaluations</li>
            </ul>
            <div class="mt-6">
                <a href="{{ route('school.portal.student.login', $website->slug) }}" class="inline-flex rounded-xl px-5 py-2.5 text-sm font-bold" style="background:var(--brand-secondary);color:var(--brand-primary)">Student login</a>
            </div>
            <p class="mt-4 text-xs text-gray-500">Student accounts are created when your application is approved and you are enrolled.</p>
        </div>
    </div>
</section>
@endsection
