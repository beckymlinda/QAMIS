<?php

namespace App\Http\Controllers;

use App\Models\OrgUnit;
use App\Support\InstitutionScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrgUnitController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', OrgUnit::class);
        $orgUnits = InstitutionScope::apply(OrgUnit::query())
            ->with('parent')
            ->orderBy('type')
            ->orderBy('name')
            ->paginate(30);

        return view('org-units.index', compact('orgUnits'));
    }

    public function create(): View
    {
        $this->authorize('create', OrgUnit::class);
        $parents = InstitutionScope::apply(OrgUnit::query())->orderBy('name')->get();

        return view('org-units.create', compact('parents'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', OrgUnit::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:college,institute,faculty,school,department,centre',
            'parent_id' => 'nullable|exists:org_units,id',
        ]);

        $validated['institution_id'] = auth()->user()->institution_id;

        OrgUnit::create($validated);

        return redirect()->route('org-units.index')->with('success', 'Organizational unit created.');
    }
}
