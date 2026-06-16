<?php

namespace App\Http\Controllers;

use App\Models\CorrectiveAction;
use App\Models\EvidenceCategory;
use App\Models\EvidenceDocument;
use App\Models\EvidenceDocumentVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EvidenceController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', EvidenceDocument::class);
        $documents = EvidenceDocument::with(['category', 'currentVersion'])->latest()->paginate(20);

        return view('evidence.index', compact('documents'));
    }

    public function create(): View
    {
        $this->authorize('create', EvidenceDocument::class);
        $categories = EvidenceCategory::orderBy('name')->get();

        return view('evidence.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', EvidenceDocument::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'evidence_category_id' => 'required|exists:evidence_categories,id',
            'description' => 'nullable|string',
            'file' => 'required|file|max:10240',
        ]);

        $document = EvidenceDocument::create([
            'institution_id' => auth()->user()->institution_id,
            'evidence_category_id' => $validated['evidence_category_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
        ]);

        $file = $request->file('file');
        $path = $file->store("evidence/{$document->institution_id}", 'local');

        $version = EvidenceDocumentVersion::create([
            'evidence_document_id' => $document->id,
            'version_no' => 1,
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'checksum' => hash_file('sha256', $file->getRealPath()),
            'uploaded_by' => auth()->id(),
            'uploaded_at' => now(),
        ]);

        $document->update(['current_version_id' => $version->id]);

        return redirect()->route('evidence.index')->with('success', 'Evidence uploaded.');
    }
}
