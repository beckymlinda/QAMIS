<?php

namespace App\Http\Controllers;

use App\Models\CorrectiveAction;
use App\Models\Recommendation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CorrectiveActionController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', CorrectiveAction::class);
        $actions = CorrectiveAction::with(['recommendation', 'assignee'])->latest()->paginate(20);

        return view('corrective-actions.index', compact('actions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', CorrectiveAction::class);

        $validated = $request->validate([
            'recommendation_id' => 'required|exists:recommendations,id',
            'assigned_to' => 'nullable|exists:users,id',
            'deadline' => 'nullable|date',
        ]);

        CorrectiveAction::create([
            ...$validated,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Corrective action assigned.');
    }

    public function update(Request $request, CorrectiveAction $correctiveAction): RedirectResponse
    {
        $this->authorize('update', $correctiveAction);

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'progress_notes' => 'nullable|string',
        ]);

        $correctiveAction->update([
            ...$validated,
            'completed_at' => $validated['status'] === 'completed' ? now() : null,
            'escalated_at' => $validated['status'] === 'pending' && $correctiveAction->deadline?->isPast()
                ? now() : $correctiveAction->escalated_at,
        ]);

        return back()->with('success', 'Corrective action updated.');
    }
}
