<?php

namespace App\Http\Controllers;

use App\Models\GovernanceMember;
use App\Models\Institution;
use App\Models\InstitutionContact;
use App\Models\InstitutionProfile;
use App\Models\StaffMember;
use App\Models\StudentEnrolment;
use App\Support\GovernanceBodyType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstitutionReportDataController extends Controller
{
    public function index(Institution $institution): View
    {
        $this->authorize('update', $institution);

        $institution->load([
            'profile',
            'contact',
            'governanceMembers' => fn ($q) => $q->orderBy('body_type')->orderBy('sort_order'),
            'staffMembers.programme',
            'studentEnrolments.programme',
            'programmes',
            'orgUnits',
        ]);

        $profile = $institution->profile ?? new InstitutionProfile(['institution_id' => $institution->id]);
        $contact = $institution->contact ?? new InstitutionContact(['institution_id' => $institution->id]);

        return view('institutions.report-data.index', [
            'institution' => $institution,
            'profile' => $profile,
            'contact' => $contact,
            'governanceBodyTypes' => GovernanceBodyType::labels(),
            'governanceByBody' => $institution->governanceMembers->groupBy('body_type'),
        ]);
    }

    public function update(Request $request, Institution $institution): RedirectResponse
    {
        $this->authorize('update', $institution);

        $validated = $request->validate([
            'executive_summary' => 'nullable|string',
            'abbreviations_acronyms' => 'nullable|string',
            'introduction_approach' => 'nullable|string',
            'assessment_team_composition' => 'nullable|string',
            'vision' => 'nullable|string',
            'mission' => 'nullable|string',
            'core_values' => 'nullable|string',
            'core_function' => 'nullable|string',
            'background_narrative' => 'nullable|string',
            'strategic_plan_summary' => 'nullable|string',
            'policies_procedures_summary' => 'nullable|string',
            'swot_strengths' => 'nullable|string',
            'swot_weaknesses' => 'nullable|string',
            'swot_opportunities' => 'nullable|string',
            'swot_threats' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
        ]);

        InstitutionProfile::updateOrCreate(
            ['institution_id' => $institution->id],
            [
                'executive_summary' => $validated['executive_summary'] ?? null,
                'abbreviations_acronyms' => $validated['abbreviations_acronyms'] ?? null,
                'introduction_approach' => $validated['introduction_approach'] ?? null,
                'assessment_team_composition' => $validated['assessment_team_composition'] ?? null,
                'vision' => $validated['vision'] ?? null,
                'mission' => $validated['mission'] ?? null,
                'core_values' => $validated['core_values'] ?? null,
                'core_function' => $validated['core_function'] ?? null,
                'background_narrative' => $validated['background_narrative'] ?? null,
                'strategic_plan_summary' => $validated['strategic_plan_summary'] ?? null,
                'policies_procedures_summary' => $validated['policies_procedures_summary'] ?? null,
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
                'email' => $validated['email'] ?? null,
                'telephone' => $validated['telephone'] ?? null,
                'website' => $validated['website'] ?? null,
            ]
        );

        return back()->with('success', 'Report narrative and background saved.');
    }

    public function storeGovernanceMember(Request $request, Institution $institution): RedirectResponse
    {
        $this->authorize('update', $institution);

        $validated = $request->validate([
            'body_type' => 'required|string|in:'.implode(',', array_keys(GovernanceBodyType::labels())),
            'name' => 'required|string|max:255',
            'gender' => 'nullable|string|in:male,female,other',
            'qualification' => 'nullable|string|max:255',
            'awarding_institution' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'experience_years' => 'nullable|integer|min:0|max:60',
        ]);

        $institution->governanceMembers()->create($validated);

        return back()->with('success', 'Governance member added.');
    }

    public function destroyGovernanceMember(Institution $institution, GovernanceMember $governanceMember): RedirectResponse
    {
        $this->authorize('update', $institution);
        abort_unless($governanceMember->institution_id === $institution->id, 404);

        $governanceMember->delete();

        return back()->with('success', 'Governance member removed.');
    }

    public function storeStaffMember(Request $request, Institution $institution): RedirectResponse
    {
        $this->authorize('update', $institution);

        $validated = $request->validate([
            'programme_id' => 'nullable|exists:programmes,id',
            'type' => 'required|string|in:academic,administrative',
            'name' => 'required|string|max:255',
            'gender' => 'nullable|string|in:male,female,other',
            'qualification' => 'nullable|string|max:255',
            'awarding_institution' => 'nullable|string|max:255',
            'qualification_year' => 'nullable|integer|min:1950|max:2100',
            'rank' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'employment_type' => 'nullable|string|in:full-time,part-time',
            'courses_taught' => 'nullable|string|max:500',
            'experience_years' => 'nullable|integer|min:0|max:60',
        ]);

        if (! empty($validated['programme_id'])) {
            abort_unless(
                $institution->programmes()->where('id', $validated['programme_id'])->exists(),
                422
            );
        }

        $institution->staffMembers()->create($validated);

        return back()->with('success', 'Staff member added.');
    }

    public function destroyStaffMember(Institution $institution, StaffMember $staffMember): RedirectResponse
    {
        $this->authorize('update', $institution);
        abort_unless($staffMember->institution_id === $institution->id, 404);

        $staffMember->delete();

        return back()->with('success', 'Staff member removed.');
    }

    public function storeStudentEnrolment(Request $request, Institution $institution): RedirectResponse
    {
        $this->authorize('update', $institution);

        $validated = $request->validate([
            'programme_id' => 'nullable|exists:programmes,id',
            'qualification_type' => 'required|string|max:255',
            'delivery_mode' => 'nullable|string|max:255',
            'male_count' => 'required|integer|min:0',
            'female_count' => 'required|integer|min:0',
            'citizenship' => 'nullable|string|max:255',
            'reporting_year' => 'nullable|string|max:4',
        ]);

        if (! empty($validated['programme_id'])) {
            abort_unless(
                $institution->programmes()->where('id', $validated['programme_id'])->exists(),
                422
            );
        }

        $institution->studentEnrolments()->create($validated);

        return back()->with('success', 'Student enrolment record added.');
    }

    public function destroyStudentEnrolment(Institution $institution, StudentEnrolment $studentEnrolment): RedirectResponse
    {
        $this->authorize('update', $institution);
        abort_unless($studentEnrolment->institution_id === $institution->id, 404);

        $studentEnrolment->delete();

        return back()->with('success', 'Student enrolment record removed.');
    }
}
