<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Applicant login — {{ $website->displayName() }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="flex min-h-screen items-center justify-center px-4 py-12">
        <div class="w-full max-w-md rounded-2xl bg-white p-8 shadow-sm ring-1 ring-gray-100">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ $website->displayName() }}</p>
            <h1 class="mt-1 text-2xl font-bold text-gray-900">Applicant login</h1>
            @auth
                @unless(auth()->user()->isApplicant())
                    <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                        You are logged in as <strong>{{ auth()->user()->name }}</strong> (admin/staff).
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="font-semibold underline">Log out</a> first to use applicant login.
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
                    </div>
                @endunless
            @endauth
            @include('partials.alerts')
            <form method="POST" action="{{ url('/school/'.$website->slug.'/apply/login') }}" class="mt-6 space-y-4">
                @csrf
                <div><label class="text-sm font-medium">Email</label><input type="email" name="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-xl border-gray-300"></div>
                <div><label class="text-sm font-medium">Password</label><input type="password" name="password" required class="mt-1 w-full rounded-xl border-gray-300"></div>
                <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="remember" class="rounded border-gray-300"> Remember me</label>
                <button type="submit" class="w-full rounded-xl bg-[#0f2744] py-3 text-sm font-bold text-white">Log in</button>
            </form>
            <p class="mt-4 text-center text-sm text-gray-600">
                New applicant?
                <a href="{{ route('school.apply.register', $website->slug) }}" class="font-semibold text-[#0f2744]">Create account</a>
            </p>
            <p class="mt-2 text-center text-sm"><a href="{{ route('school.applications', $website->slug) }}" class="text-gray-500">← Back to application info</a></p>
        </div>
    </div>
</body>
</html>
