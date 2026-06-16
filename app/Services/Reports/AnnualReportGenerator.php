<?php

namespace App\Services\Reports;

use App\Models\GeneratedReport;
use App\Models\Institution;
use App\Models\ReportTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

class AnnualReportGenerator
{
    public function generate(Institution $institution, int $year = null): GeneratedReport
    {
        $year = $year ?? (int) date('Y');
        $template = ReportTemplate::where('type', 'annual')->where('is_active', true)->firstOrFail();

        $institution->load([
            'profile', 'contact', 'programmes.orgUnit', 'governanceMembers',
            'staffMembers', 'orgUnits', 'assessments.complianceResult',
        ]);

        $snapshot = [
            'year' => $year,
            'institution' => $institution->toArray(),
            'profile' => $institution->profile?->toArray(),
            'contact' => $institution->contact?->toArray(),
            'programmes' => $institution->programmes->toArray(),
            'staff' => $institution->staffMembers->toArray(),
            'governance' => $institution->governanceMembers->groupBy('body_type')->toArray(),
            'compliance' => $institution->assessments->map(fn ($a) => $a->complianceResult?->toArray())->filter()->values()->toArray(),
        ];

        $report = GeneratedReport::create([
            'institution_id' => $institution->id,
            'report_template_id' => $template->id,
            'reporting_year' => (string) $year,
            'status' => 'generated',
            'generated_by' => auth()->id(),
            'snapshot_data' => $snapshot,
        ]);

        $html = view('reports.annual', compact('snapshot', 'institution', 'year'))->render();
        $pdfPath = "reports/annual-{$institution->id}-{$year}-".time().'.pdf';
        Storage::disk('local')->put($pdfPath, Pdf::loadHTML($html)->output());
        $report->file_pdf_path = $pdfPath;

        $phpWord = new PhpWord;
        $section = $phpWord->addSection();
        $section->addTitle('Annual Report / Institutional Audit', 1);
        $section->addText($institution->name);
        $section->addText('Year: '.$year);
        $path = "reports/annual-{$institution->id}-{$year}-".time().'.docx';
        IOFactory::createWriter($phpWord, 'Word2007')->save(Storage::disk('local')->path($path));
        $report->file_docx_path = $path;
        $report->save();

        return $report;
    }
}
