<?php

namespace App\Http\Controllers;

use App\Models\ExternalEvaluatorInvitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ExternalEvaluatorController extends Controller
{
    public function invite(Request $request): RedirectResponse
    {
        $this->authorize('create', ExternalEvaluatorInvitation::class);

        $validated = $request->validate([
            'email' => 'required|email',
            'institution_id' => 'required|exists:institutions,id',
            'assessment_id' => 'nullable|exists:assessments,id',
        ]);

        $token = Str::random(64);

        ExternalEvaluatorInvitation::create([
            ...$validated,
            'token' => hash('sha256', $token),
            'expires_at' => now()->addDays(14),
        ]);

        return back()->with('success', 'Invitation created. Share link: '.url('/evaluator/accept/'.$token));
    }

    public function accept(string $token): RedirectResponse
    {
        $hashed = hash('sha256', $token);
        $invitation = ExternalEvaluatorInvitation::where('token', $hashed)
            ->where('expires_at', '>', now())
            ->whereNull('accepted_at')
            ->firstOrFail();

        $user = User::firstOrCreate(
            ['email' => $invitation->email, 'institution_id' => $invitation->institution_id],
            [
                'name' => 'External Evaluator',
                'password' => Hash::make(Str::random(32)),
                'is_active' => true,
            ]
        );

        $user->assignRole('external_evaluator');
        $invitation->update(['accepted_at' => now(), 'user_id' => $user->id]);

        auth()->login($user);

        return redirect()->route('dashboard')->with('success', 'External evaluator access granted.');
    }
}
