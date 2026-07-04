<?php

namespace App\Http\Controllers;

use App\Models\GeneratedReport;
use App\Models\Institution;
use App\Services\Reports\AnnualReportGenerator;
use App\Services\Reports\SelfAssessmentReportGenerator;
use App\Support\InstitutionScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', GeneratedReport::class);
        $reports = InstitutionScope::apply(GeneratedReport::query())
            ->with('template')
            ->latest()
            ->paginate(20);

        return view('reports.index', compact('reports'));
    }

    public function generateSar(Request $request, SelfAssessmentReportGenerator $generator): RedirectResponse
    {
        $this->authorize('generate', GeneratedReport::class);

        $institution = Institution::findOrFail(
            auth()->user()->institution_id ?? $request->session()->get('active_institution_id')
        );

        abort_unless(
            auth()->user()->isNcheOrSystemAdmin() || auth()->user()->institution_id === $institution->id,
            403
        );

        $report = $generator->generate($institution, null, (int) ($request->year ?? date('Y')));

        return redirect()->route('reports.show', $report)->with('success', 'Self-Assessment Report generated.');
    }

    public function generateAnnual(Request $request, AnnualReportGenerator $generator): RedirectResponse
    {
        $this->authorize('generate', GeneratedReport::class);

        $institution = Institution::findOrFail(
            auth()->user()->institution_id ?? $request->session()->get('active_institution_id')
        );

        abort_unless(
            auth()->user()->isNcheOrSystemAdmin() || auth()->user()->institution_id === $institution->id,
            403
        );

        $report = $generator->generate($institution, (int) ($request->year ?? date('Y')));

        return redirect()->route('reports.show', $report)->with('success', 'Annual Report generated.');
    }

    public function show(GeneratedReport $report): View
    {
        $this->authorize('view', $report);

        return view('reports.show', compact('report'));
    }

    public function download(GeneratedReport $report, string $format): StreamedResponse
    {
        $this->authorize('view', $report);

        $path = $format === 'pdf' ? $report->file_pdf_path : $report->file_docx_path;

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path);
    }
}
