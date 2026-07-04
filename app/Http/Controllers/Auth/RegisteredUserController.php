<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'institution_name' => ['required', 'string', 'max:255'],
            'institution_acronym' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($request) {
            $institution = Institution::create([
                'name' => $request->institution_name,
                'acronym' => $request->institution_acronym,
                'status' => 'active',
            ]);

            $user = User::create([
                'institution_id' => $institution->id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_active' => true,
            ]);

            Role::firstOrCreate(['name' => 'institution_admin', 'guard_name' => 'web']);
            $user->assignRole('institution_admin');

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect()
            ->route('institutions.report-data.index', $user->institution_id)
            ->with('success', 'Welcome! Set up your institution report data to get started.');
    }
}
