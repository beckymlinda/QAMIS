<?php

namespace App\Http\Controllers;

use App\Enums\ProgrammeApplicationStatus;
use App\Models\ProgrammeApplication;
use App\Services\ApplicationEnrollmentService;
use App\Support\InstitutionScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProgrammeApplicationController extends Controller
{
    public function __construct(
        protected ApplicationEnrollmentService $enrollment,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ProgrammeApplication::class);

        $query = InstitutionScope::apply(ProgrammeApplication::query())
            ->with(['programme', 'user'])
            ->latest('submitted_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('programme_id')) {
            $query->where('programme_id', $request->integer('programme_id'));
        }

        $applications = $query->paginate(20)->withQueryString();
        $statuses = ProgrammeApplicationStatus::options();
        $programmes = InstitutionScope::apply(\App\Models\Programme::query())->orderBy('name')->get(['id', 'name']);

        $stats = [
            'total' => InstitutionScope::apply(ProgrammeApplication::query())->count(),
            'submitted' => InstitutionScope::apply(ProgrammeApplication::query())->where('status', ProgrammeApplicationStatus::Submitted)->count(),
            'under_review' => InstitutionScope::apply(ProgrammeApplication::query())->where('status', ProgrammeApplicationStatus::UnderReview)->count(),
            'approved' => InstitutionScope::apply(ProgrammeApplication::query())->where('status', ProgrammeApplicationStatus::Approved)->count(),
            'pending_payment' => InstitutionScope::apply(ProgrammeApplication::query())->whereNull('payment_verified_at')->count(),
        ];

        return view('applications.index', compact('applications', 'statuses', 'programmes', 'stats'));
    }

    public function show(ProgrammeApplication $application): View
    {
        $this->authorize('view', $application);

        $application->load(['programme.orgUnit', 'user', 'paymentVerifier', 'reviewer', 'enrolledStudent']);
        $statuses = ProgrammeApplicationStatus::options();

        return view('applications.show', compact('application', 'statuses'));
    }

    public function updateStatus(Request $request, ProgrammeApplication $application): RedirectResponse
    {
        $this->authorize('update', $application);

        $validated = $request->validate([
            'status' => 'required|in:'.implode(',', array_column(ProgrammeApplicationStatus::cases(), 'value')),
            'admin_notes' => 'nullable|string|max:5000',
        ]);

        $status = ProgrammeApplicationStatus::from($validated['status']);

        if ($status === ProgrammeApplicationStatus::Enrolled) {
            $yearOfStudy = (int) ($request->input('year_of_study', 1));

            if ($validated['admin_notes'] ?? null) {
                $application->update([
                    'admin_notes' => $validated['admin_notes'],
                    'reviewed_by' => auth()->id(),
                ]);
            }

            $student = $this->enrollment->enroll($application, $yearOfStudy, allowWithoutApproval: true);

            return back()->with('success', 'Applicant enrolled as student '.$student->student_number.'. Student portal access is now active.');
        }

        $application->update([
            'status' => $status,
            'admin_notes' => $validated['admin_notes'] ?? $application->admin_notes,
            'reviewed_by' => auth()->id(),
            'decision_at' => in_array($status, [
                ProgrammeApplicationStatus::Approved,
                ProgrammeApplicationStatus::Rejected,
                ProgrammeApplicationStatus::WaitingList,
            ], true) ? now() : $application->decision_at,
        ]);

        return back()->with('success', 'Application status updated.');
    }

    public function verifyPayment(ProgrammeApplication $application): RedirectResponse
    {
        $this->authorize('update', $application);

        abort_unless($application->payment_proof_path, 422, 'No payment proof uploaded.');

        $application->update([
            'payment_verified_at' => now(),
            'payment_verified_by' => auth()->id(),
            'status' => $application->status === ProgrammeApplicationStatus::Submitted
                ? ProgrammeApplicationStatus::UnderReview
                : $application->status,
        ]);

        return back()->with('success', 'Application fee payment verified.');
    }

    public function enroll(Request $request, ProgrammeApplication $application): RedirectResponse
    {
        $this->authorize('update', $application);

        $validated = $request->validate([
            'year_of_study' => 'nullable|integer|min:1|max:10',
        ]);

        $student = $this->enrollment->enroll($application, (int) ($validated['year_of_study'] ?? 1));

        return redirect()
            ->route('students.show', $student)
            ->with('success', 'Applicant enrolled as student '.$student->student_number.'. Student portal access is now active.');
    }

    public function downloadFile(ProgrammeApplication $application, string $field): StreamedResponse
    {
        $this->authorize('view', $application);

        return $this->fileResponse($application, $field, true);
    }

    public function previewFile(ProgrammeApplication $application, string $field): StreamedResponse|\Illuminate\Http\Response
    {
        $this->authorize('view', $application);

        return $this->fileResponse($application, $field, false);
    }

    protected function fileResponse(ProgrammeApplication $application, string $field, bool $download): StreamedResponse|\Illuminate\Http\Response
    {
        $column = \App\Support\ApplicationDocuments::columnFor($field);
        abort_unless($column, 404);

        $path = $application->{$column};
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        if ($download) {
            return Storage::disk('local')->download($path);
        }

        $mime = Storage::disk('local')->mimeType($path) ?: 'application/octet-stream';

        return response(Storage::disk('local')->get($path), 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.basename($path).'"',
        ]);
    }
}
