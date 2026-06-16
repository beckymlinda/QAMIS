<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\InstitutionContact;
use App\Models\InstitutionProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstitutionProfileController extends Controller
{
    public function edit(Institution $institution): View
    {
        $this->authorize('update', $institution);
        $profile = $institution->profile ?? new InstitutionProfile(['institution_id' => $institution->id]);
        $contact = $institution->contact ?? new InstitutionContact(['institution_id' => $institution->id]);

        return view('institutions.profile.edit', compact('institution', 'profile', 'contact'));
    }

    public function update(Request $request, Institution $institution): RedirectResponse
    {
        $this->authorize('update', $institution);

        $validated = $request->validate([
            'vision' => 'nullable|string',
            'mission' => 'nullable|string',
            'core_values' => 'nullable|string',
            'strategic_plan_summary' => 'nullable|string',
            'background_narrative' => 'nullable|string',
            'swot_strengths' => 'nullable|string',
            'swot_weaknesses' => 'nullable|string',
            'swot_opportunities' => 'nullable|string',
            'swot_threats' => 'nullable|string',
            'postal_address' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
        ]);

        InstitutionProfile::updateOrCreate(
            ['institution_id' => $institution->id],
            [
                'vision' => $validated['vision'] ?? null,
                'mission' => $validated['mission'] ?? null,
                'core_values' => $validated['core_values'] ?? null,
                'strategic_plan_summary' => $validated['strategic_plan_summary'] ?? null,
                'background_narrative' => $validated['background_narrative'] ?? null,
                'swot_analysis' => [
                    'strengths' => $validated['swot_strengths'] ?? '',
                    'weaknesses' => $validated['swot_weaknesses'] ?? '',
                    'opportunities' => $validated['swot_opportunities'] ?? '',
                    'threats' => $validated['swot_threats'] ?? '',
                ],
            ]
        );

        InstitutionContact::updateOrCreate(
            ['institution_id' => $institution->id],
            [
                'postal_address' => $validated['postal_address'] ?? null,
                'email' => $validated['email'] ?? null,
                'telephone' => $validated['telephone'] ?? null,
                'website' => $validated['website'] ?? null,
            ]
        );

        return back()->with('success', 'Institution profile updated.');
    }
}
