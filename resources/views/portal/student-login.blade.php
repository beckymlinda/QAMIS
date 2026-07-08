<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student login — {{ $website->displayName() }}</title>
    @php $brand = $website->branding(); @endphp
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>:root { --brand-primary: {{ $brand['primary'] }}; --brand-secondary: {{ $brand['secondary'] }}; }</style>
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="flex min-h-screen flex-col">
        <header class="border-b border-gray-200 bg-white px-4 py-4">
            <div class="mx-auto flex max-w-md items-center justify-between">
                <a href="{{ route('school.portal', $website->slug) }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900">← Student portal</a>
                <span class="text-sm font-bold" style="color:var(--brand-primary)">{{ $website->displayName() }}</span>
            </div>
        </header>
        <div class="flex flex-1 items-center justify-center px-4 py-12">
            <div class="w-full max-w-md rounded-2xl bg-white p-8 shadow-sm ring-1 ring-gray-100">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Enrolled students</p>
                <h1 class="mt-1 text-2xl font-bold text-gray-900">Student login</h1>
                <p class="mt-2 text-sm text-gray-600">Sign in to access your courses, timetable, and results at {{ $website->displayName() }}.</p>
                @auth
                    @unless(auth()->user()->hasRole('student'))
                        <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                            You are logged in as <strong>{{ auth()->user()->name }}</strong>.
                            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="font-semibold underline">Log out</a> first to use student login.
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
                        </div>
                    @endunless
                @endauth
                @include('partials.alerts')
                <form method="POST" action="{{ url('/school/'.$website->slug.'/portal/student/login') }}" class="mt-6 space-y-4">
                    @csrf
                    <div><label class="text-sm font-medium">Email</label><input type="email" name="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-xl border-gray-300"></div>
                    <div><label class="text-sm font-medium">Password</label><input type="password" name="password" required class="mt-1 w-full rounded-xl border-gray-300"></div>
                    <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="remember" class="rounded border-gray-300"> Remember me</label>
                    <button type="submit" class="w-full rounded-xl py-3 text-sm font-bold text-white" style="background:var(--brand-primary, #0f2744)">Log in to student portal</button>
                </form>
                <p class="mt-4 text-center text-sm text-gray-600">
                    Applying for admission?
                    <a href="{{ route('school.apply.login', $website->slug) }}" class="font-semibold" style="color:var(--brand-primary)">Applicant login</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
