<?php

namespace App\Http\Controllers;

use App\Models\InstitutionWebsiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SchoolPortalAuthController extends Controller
{
    protected function website(string $slug): InstitutionWebsiteSetting
    {
        return InstitutionWebsiteSetting::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->with('institution')
            ->firstOrFail();
    }

    public function showStudentLogin(string $slug): View|RedirectResponse
    {
        $website = $this->website($slug);

        if (Auth::check()) {
            $user = Auth::user();
            if ($user->hasRole('student') && $user->institution_id === $website->institution_id) {
                return redirect()->route('student.dashboard');
            }
        }

        return view('portal.student-login', compact('website'));
    }

    public function studentLogin(Request $request, string $slug): RedirectResponse
    {
        $website = $this->website($slug);

        if (Auth::check() && ! Auth::user()->hasRole('student')) {
            return back()->withErrors([
                'email' => 'You are logged in with a different account type. Log out first, then use student login.',
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
            $user->hasRole('student') && $user->institution_id === $website->institution_id,
            403,
            'This login is for enrolled students at '.$website->displayName().'.'
        );

        return redirect()->route('student.dashboard');
    }
}
