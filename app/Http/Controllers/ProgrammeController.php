<?php

namespace App\Http\Controllers;

use App\Models\OrgUnit;
use App\Models\Programme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgrammeController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Programme::class);
        $programmes = Programme::with('orgUnit')->orderBy('name')->paginate(20);

        return view('programmes.index', compact('programmes'));
    }

    public function create(): View
    {
        $this->authorize('create', Programme::class);
        $orgUnits = OrgUnit::orderBy('name')->get();

        return view('programmes.create', compact('orgUnits'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Programme::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'org_unit_id' => 'nullable|exists:org_units,id',
            'level' => 'required|string',
            'delivery_modes' => 'nullable|array',
            'nche_accreditation_status' => 'nullable|string',
            'professional_body' => 'nullable|string|max:255',
            'curriculum_developed_at' => 'nullable|date',
            'curriculum_reviewed_at' => 'nullable|date',
        ]);

        $validated['institution_id'] = auth()->user()->institution_id;

        Programme::create($validated);

        return redirect()->route('programmes.index')->with('success', 'Programme registered.');
    }

    public function show(Programme $programme): View
    {
        $this->authorize('view', $programme);
        $programme->load(['orgUnit', 'assessments.complianceResult']);

        return view('programmes.show', compact('programme'));
    }
}
