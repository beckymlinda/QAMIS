<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\EvidenceDocument;
use App\Models\GovernanceMember;
use App\Models\Programme;
use App\Models\StaffMember;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $query = $request->string('q')->trim()->toString();
        $results = collect();

        if (strlen($query) >= 2) {
            $institutionId = auth()->user()->institution_id ?? $request->session()->get('active_institution_id');

            $programmes = Programme::query()
                ->when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))
                ->where('name', 'like', "%{$query}%")
                ->limit(10)->get()
                ->map(fn ($p) => ['type' => 'Programme', 'label' => $p->name, 'url' => route('programmes.show', $p)]);

            $assessments = Assessment::query()
                ->when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))
                ->where('title', 'like', "%{$query}%")
                ->limit(10)->get()
                ->map(fn ($a) => ['type' => 'Assessment', 'label' => $a->title, 'url' => route('assessments.show', $a)]);

            $evidence = EvidenceDocument::query()
                ->when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))
                ->where('title', 'like', "%{$query}%")
                ->limit(10)->get()
                ->map(fn ($e) => ['type' => 'Evidence', 'label' => $e->title, 'url' => route('evidence.index')]);

            $staff = StaffMember::query()
                ->when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))
                ->where('name', 'like', "%{$query}%")
                ->limit(10)->get()
                ->map(fn ($s) => ['type' => 'Staff', 'label' => $s->name, 'url' => route('dashboard')]);

            $results = $programmes->concat($assessments)->concat($evidence)->concat($staff);
        }

        return view('search.index', compact('query', 'results'));
    }
}
