<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create applicant account — {{ $website->displayName() }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="flex min-h-screen items-center justify-center px-4 py-12">
        <div class="w-full max-w-md rounded-2xl bg-white p-8 shadow-sm ring-1 ring-gray-100">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Apply to {{ $website->displayName() }}</p>
            <h1 class="mt-1 text-2xl font-bold text-gray-900">Create applicant account</h1>
            <p class="mt-2 text-sm text-gray-600">This account is for admission applications only — not institution registration.</p>
            @auth
                @unless(auth()->user()->isApplicant())
                    <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                        You are logged in as <strong>{{ auth()->user()->name }}</strong> (admin/staff).
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="font-semibold underline">Log out</a> to create an applicant account.
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
                    </div>
                @endunless
            @endauth
            @include('partials.alerts')
            <form method="POST" action="{{ url('/school/'.$website->slug.'/apply/register') }}" class="mt-6 space-y-4">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="text-sm font-medium">First name</label><input name="first_name" value="{{ old('first_name') }}" required class="mt-1 w-full rounded-xl border-gray-300"></div>
                    <div><label class="text-sm font-medium">Last name</label><input name="last_name" value="{{ old('last_name') }}" required class="mt-1 w-full rounded-xl border-gray-300"></div>
                </div>
                <div><label class="text-sm font-medium">Email</label><input type="email" name="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-xl border-gray-300"></div>
                <div><label class="text-sm font-medium">Phone</label><input name="phone" value="{{ old('phone') }}" class="mt-1 w-full rounded-xl border-gray-300"></div>
                <div><label class="text-sm font-medium">Password</label><input type="password" name="password" required class="mt-1 w-full rounded-xl border-gray-300"></div>
                <div><label class="text-sm font-medium">Confirm password</label><input type="password" name="password_confirmation" required class="mt-1 w-full rounded-xl border-gray-300"></div>
                <button type="submit" class="w-full rounded-xl bg-[#0f2744] py-3 text-sm font-bold text-white">Create account &amp; continue</button>
            </form>
            <p class="mt-4 text-center text-sm text-gray-600">
                Already have an applicant account?
                <a href="{{ route('school.apply.login', $website->slug) }}" class="font-semibold text-[#0f2744]">Log in</a>
            </p>
            <p class="mt-2 text-center text-sm"><a href="{{ route('school.applications', $website->slug) }}" class="text-gray-500">← Back to application info</a></p>
        </div>
    </div>
</body>
</html>
