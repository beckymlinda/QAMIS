<?php

namespace App\Http\Controllers;

use App\Enums\ProgrammeApplicationStatus;
use App\Models\InstitutionWebsiteSetting;
use App\Models\Programme;
use App\Models\ProgrammeApplication;
use App\Services\ProgrammeApplicationService;
use App\Support\ApplicationDocuments;
use App\Support\CertificateGrades;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicantPortalController extends Controller
{
    public function __construct(
        protected ProgrammeApplicationService $applications,
    ) {}

    protected function applicant()
    {
        abort_unless(auth()->user()->isApplicant(), 403);

        return auth()->user();
    }

    protected function website(string $slug): InstitutionWebsiteSetting
    {
        return InstitutionWebsiteSetting::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->with('institution')
            ->firstOrFail();
    }

    protected function authorizeApplicantApplication(ProgrammeApplication $application): void
    {
        abort_unless($application->user_id === $this->applicant()->id, 403);
    }

    public function dashboard(): View
    {
        $user = $this->applicant();
        $applications = ProgrammeApplication::query()
            ->where('user_id', $user->id)
            ->with('programme.orgUnit')
            ->latest()
            ->get();

        $activeApplication = ProgrammeApplication::activeForUser($user->id);
        $website = InstitutionWebsiteSetting::where('institution_id', $user->institution_id)->first();

        return view('applicant.dashboard', compact('applications', 'activeApplication', 'website'));
    }

    public function createApplication(string $slug): View|RedirectResponse
    {
        $user = $this->applicant();
        $website = $this->website($slug);
        abort_unless($user->institution_id === $website->institution_id, 403);

        if ($active = ProgrammeApplication::activeForUser($user->id)) {
            return redirect()
                ->route('applicant.applications.show', $active)
                ->with('info', 'You already have an active application. You can view or edit it below.');
        }

        $programmes = $this->openProgrammes($website);
        $maxUploadMb = $website->application_upload_max_mb ?? 10;
        $gradeData = CertificateGrades::forForm(null);

        return view('applicant.apply', [
            'website' => $website,
            'programmes' => $programmes,
            'maxUploadMb' => $maxUploadMb,
            'user' => $user,
            'application' => null,
            'gradeData' => $gradeData,
        ]);
    }

    public function storeApplication(Request $request, string $slug): RedirectResponse
    {
        $user = $this->applicant();
        $website = $this->website($slug);
        abort_unless($user->institution_id === $website->institution_id, 403);

        abort_if(
            ProgrammeApplication::activeForUser($user->id),
            422,
            'You already have an active application.'
        );

        $validated = $this->validateApplicationPayload($request, $website, true);

        $programme = Programme::query()
            ->where('institution_id', $website->institution_id)
            ->findOrFail($validated['programme_id']);

        abort_unless($programme->isOpenForApplications(), 403, 'This programme is not accepting applications.');

        $application = ProgrammeApplication::create([
            'institution_id' => $website->institution_id,
            'programme_id' => $programme->id,
            'user_id' => $user->id,
            'application_number' => $this->applications->generateApplicationNumber($website->institution),
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'address' => $validated['address'] ?? null,
            'nationality' => $validated['nationality'] ?? null,
            'certificate_grades' => CertificateGrades::parseFromRequest($request->all()),
            'payment_reference' => $validated['payment_reference'] ?? null,
            'status' => ProgrammeApplicationStatus::Submitted,
            'submitted_at' => now(),
        ]);

        $this->storeUploadedDocuments($request, $application, true);

        return redirect()
            ->route('applicant.applications.show', $application)
            ->with('success', 'Application submitted successfully.');
    }

    public function editApplication(ProgrammeApplication $application): View|RedirectResponse
    {
        $this->authorizeApplicantApplication($application);
        abort_unless($application->canBeEditedByApplicant(), 403, 'This application can no longer be edited.');

        $application->load(['programme.orgUnit', 'institution']);
        $website = InstitutionWebsiteSetting::where('institution_id', $application->institution_id)->firstOrFail();
        $programmes = $this->openProgrammes($website)->push($application->programme)->unique('id')->sortBy('name')->values();
        $maxUploadMb = $website->application_upload_max_mb ?? 10;
        $gradeData = CertificateGrades::forForm($application->certificate_grades);

        return view('applicant.edit', compact('application', 'website', 'programmes', 'maxUploadMb', 'gradeData'));
    }

    public function updateApplication(Request $request, ProgrammeApplication $application): RedirectResponse
    {
        $this->authorizeApplicantApplication($application);
        abort_unless($application->canBeEditedByApplicant(), 403, 'This application can no longer be edited.');

        $website = InstitutionWebsiteSetting::where('institution_id', $application->institution_id)->firstOrFail();
        $validated = $this->validateApplicationPayload($request, $website, false);

        $programme = Programme::query()
            ->where('institution_id', $application->institution_id)
            ->findOrFail($validated['programme_id']);

        abort_unless($programme->isOpenForApplications(), 403, 'This programme is not accepting applications.');

        $application->update([
            'programme_id' => $programme->id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'address' => $validated['address'] ?? null,
            'nationality' => $validated['nationality'] ?? null,
            'certificate_grades' => CertificateGrades::parseFromRequest($request->all()),
            'payment_reference' => $validated['payment_reference'] ?? null,
        ]);

        $this->storeUploadedDocuments($request, $application, false);

        if (! $application->fresh()->hasRequiredDocuments()) {
            return back()
                ->withInput()
                ->withErrors(['documents' => 'Please ensure all required documents are uploaded before saving.']);
        }

        return redirect()
            ->route('applicant.applications.show', $application)
            ->with('success', 'Application updated successfully.');
    }

    public function showApplication(ProgrammeApplication $application): View
    {
        $this->authorizeApplicantApplication($application);

        $application->load(['programme.orgUnit', 'institution']);
        $timeline = $this->applications->timeline($application);
        $gradeData = CertificateGrades::forForm($application->certificate_grades);
        $documentTypes = ApplicationDocuments::types();

        return view('applicant.show', compact('application', 'timeline', 'gradeData', 'documentTypes'));
    }

    public function removeDocument(ProgrammeApplication $application, string $field): RedirectResponse
    {
        $this->authorizeApplicantApplication($application);
        abort_unless($application->canBeEditedByApplicant(), 403);

        $column = ApplicationDocuments::columnFor($field);
        abort_unless($column, 404);

        $this->applications->deleteUpload($application->{$column});
        $application->update([$column => null]);

        return back()->with('success', ApplicationDocuments::labelFor($field).' removed.');
    }

    public function downloadFile(ProgrammeApplication $application, string $field): StreamedResponse
    {
        $this->authorizeApplicantApplication($application);

        return $this->fileResponse($application, $field, true);
    }

    public function previewFile(ProgrammeApplication $application, string $field): Response|StreamedResponse
    {
        $this->authorizeApplicantApplication($application);

        return $this->fileResponse($application, $field, false);
    }

    public function profile(): View
    {
        $user = $this->applicant();

        return view('applicant.profile', compact('user'));
    }

    /** @return \Illuminate\Support\Collection<int, Programme> */
    protected function openProgrammes(InstitutionWebsiteSetting $website)
    {
        return Programme::query()
            ->where('institution_id', $website->institution_id)
            ->with('orgUnit')
            ->orderBy('name')
            ->get()
            ->filter(fn (Programme $p) => $p->isOpenForApplications())
            ->values();
    }

    /** @return array<string, mixed> */
    protected function validateApplicationPayload(Request $request, InstitutionWebsiteSetting $website, bool $isCreate): array
    {
        $maxKb = ($website->application_upload_max_mb ?? 10) * 1024;

        $rules = array_merge([
            'programme_id' => 'required|exists:programmes,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'nationality' => 'nullable|string|max:100',
            'payment_reference' => 'nullable|string|max:100',
        ], CertificateGrades::validationRules());

        foreach (ApplicationDocuments::types() as $field => $meta) {
            $mimes = $field === 'photo' ? 'jpg,jpeg,png' : 'pdf,jpg,jpeg,png';
            $rule = 'nullable|file|mimes:'.$mimes.'|max:'.$maxKb;
            if ($meta['required'] && $isCreate) {
                $rule = 'required|file|mimes:'.$mimes.'|max:'.$maxKb;
            }
            $rules[$field] = $rule;
        }

        return $request->validate($rules);
    }

    protected function storeUploadedDocuments(Request $request, ProgrammeApplication $application, bool $isCreate): void
    {
        foreach (ApplicationDocuments::types() as $field => $meta) {
            if (! $request->hasFile($field)) {
                continue;
            }

            $this->applications->deleteUpload($application->{$meta['column']});
            $application->update([
                $meta['column'] => $this->applications->storeUpload($application, $request->file($field), $field),
            ]);
        }

        if ($isCreate) {
            abort_unless($application->fresh()->hasRequiredDocuments(), 422, 'Please upload all required documents.');
        }
    }

    protected function fileResponse(ProgrammeApplication $application, string $field, bool $download): StreamedResponse|Response
    {
        $column = ApplicationDocuments::columnFor($field);
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
