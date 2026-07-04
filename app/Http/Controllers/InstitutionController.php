<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Support\InstitutionContext;
use App\Support\InstitutionScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstitutionController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $this->authorize('viewAny', Institution::class);

        $user = $request->user();
        if ($user->institution_id && ! $user->isNcheOrSystemAdmin()) {
            return redirect()->route('institutions.show', $user->institution_id);
        }

        $institutions = InstitutionScope::apply(Institution::query())
            ->orderBy('name')
            ->paginate(15);

        return view('institutions.index', compact('institutions'));
    }

    public function create(): View
    {
        $this->authorize('create', Institution::class);

        return view('institutions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Institution::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'acronym' => 'nullable|string|max:50',
            'establishment_year' => 'nullable|integer|min:1800|max:2100',
            'web_address' => 'nullable|url|max:255',
        ]);

        $institution = Institution::create($validated);

        return redirect()->route('institutions.show', $institution)
            ->with('success', 'Institution created successfully.');
    }

    public function show(Institution $institution): View
    {
        $this->authorize('view', $institution);
        $institution->load(['profile', 'contact', 'campuses', 'programmes']);

        return view('institutions.show', compact('institution'));
    }

    public function edit(Institution $institution): View
    {
        $this->authorize('update', $institution);

        return view('institutions.edit', compact('institution'));
    }

    public function update(Request $request, Institution $institution): RedirectResponse
    {
        $this->authorize('update', $institution);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'acronym' => 'nullable|string|max:50',
            'establishment_year' => 'nullable|integer|min:1800|max:2100',
            'web_address' => 'nullable|url|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $institution->update($validated);

        return redirect()->route('institutions.show', $institution)
            ->with('success', 'Institution updated.');
    }

    public function select(Request $request): RedirectResponse
    {
        $request->validate(['institution_id' => 'required|exists:institutions,id']);
        $request->session()->put('active_institution_id', $request->institution_id);
        InstitutionContext::set((int) $request->institution_id);

        return back()->with('success', 'Institution context switched.');
    }
}
