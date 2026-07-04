<?php

namespace App\Http\Controllers;

use App\Models\OrgUnit;
use App\Models\Programme;
use App\Support\InstitutionScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgrammeController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Programme::class);
        $programmes = InstitutionScope::apply(Programme::query())
            ->with('orgUnit')
            ->orderBy('name')
            ->paginate(20);

        return view('programmes.index', compact('programmes'));
    }

    public function create(): View
    {
        $this->authorize('create', Programme::class);

        return view('programmes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Programme::class);

        $validated = $this->validatedProgramme($request);
        $validated['institution_id'] = auth()->user()->institution_id;
        $validated['org_unit_id'] = $this->resolveDepartmentOrgUnit(
            $request->input('department'),
            (int) auth()->user()->institution_id
        );

        Programme::create($validated);

        return redirect()->route('programmes.index')->with('success', 'Programme registered.');
    }

    public function show(Programme $programme): View
    {
        $this->authorize('view', $programme);
        $programme->load([
            'orgUnit',
            'assessments' => fn ($query) => $query->with('complianceResult')->latest(),
        ]);

        return view('programmes.show', compact('programme'));
    }

    public function edit(Programme $programme): View
    {
        $this->authorize('update', $programme);

        return view('programmes.edit', compact('programme'));
    }

    public function update(Request $request, Programme $programme): RedirectResponse
    {
        $this->authorize('update', $programme);

        $validated = $this->validatedProgramme($request);
        $validated['org_unit_id'] = $this->resolveDepartmentOrgUnit(
            $request->input('department'),
            (int) $programme->institution_id
        );

        $programme->update($validated);

        return redirect()->route('programmes.index')->with('success', 'Programme updated.');
    }

    public function destroy(Programme $programme): RedirectResponse
    {
        $this->authorize('delete', $programme);

        if ($programme->assessments()->exists()) {
            return back()->with('error', 'Cannot delete a programme that has assessments. Remove or reassign assessments first.');
        }

        $programme->delete();

        return redirect()->route('programmes.index')->with('success', 'Programme deleted.');
    }

    protected function validatedProgramme(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'level' => 'required|string',
            'delivery_modes' => 'nullable|array',
            'nche_accreditation_status' => 'nullable|string',
            'professional_body' => 'nullable|string|max:255',
            'curriculum_developed_at' => 'nullable|date',
            'curriculum_reviewed_at' => 'nullable|date',
        ]);
    }

    protected function resolveDepartmentOrgUnit(?string $departmentName, int $institutionId): ?int
    {
        $departmentName = trim((string) $departmentName);

        if ($departmentName === '') {
            return null;
        }

        $orgUnit = OrgUnit::firstOrCreate(
            [
                'institution_id' => $institutionId,
                'name' => $departmentName,
                'type' => 'department',
            ],
            ['sort_order' => 0]
        );

        return $orgUnit->id;
    }
}
