<?php

namespace App\Http\Controllers;

use App\Models\InstitutionWebsiteSetting;
use App\Models\User;
use App\Services\ApplicationEnrollmentService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class ApplicantAuthController extends Controller
{
    public function __construct(
        protected ApplicationEnrollmentService $enrollment,
    ) {}

    protected function website(string $slug): InstitutionWebsiteSetting
    {
        return InstitutionWebsiteSetting::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->with('institution')
            ->firstOrFail();
    }

    public function showRegister(string $slug): View|RedirectResponse
    {
        $website = $this->website($slug);

        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isApplicant() && $user->institution_id === $website->institution_id) {
                return redirect()->route('applicant.apply.create', $website->slug);
            }
        }

        return view('applicant.auth.register', compact('website'));
    }

    public function register(Request $request, string $slug): RedirectResponse
    {
        $website = $this->website($slug);

        if (Auth::check() && ! Auth::user()->isApplicant()) {
            return back()->withErrors([
                'email' => 'You are logged in to the admin portal. Log out first, then create an applicant account.',
            ]);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = $this->enrollment->createApplicantUser([
            'institution_id' => $website->institution_id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect()
            ->route('applicant.apply.create', $website->slug)
            ->with('success', 'Account created. Complete your application below.');
    }

    public function showLogin(string $slug): View|RedirectResponse
    {
        $website = $this->website($slug);

        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isApplicant() && $user->institution_id === $website->institution_id) {
                return redirect()->route('applicant.dashboard');
            }
        }

        return view('applicant.auth.login', compact('website'));
    }

    public function login(Request $request, string $slug): RedirectResponse
    {
        $website = $this->website($slug);

        if (Auth::check() && ! Auth::user()->isApplicant()) {
            return back()->withErrors([
                'email' => 'You are logged in to the admin portal. Log out first, then use applicant login.',
            ]);
        }

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'These credentials do not match our records.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();
        abort_unless(
            $user->hasRole('applicant') && $user->institution_id === $website->institution_id,
            403,
            'This login is for applicant accounts at '.$website->displayName().'.'
        );

        return redirect()->route('applicant.dashboard');
    }
}
