<?php

namespace App\Services\Reports;

use App\Models\Assessment;
use App\Models\GeneratedReport;
use App\Models\Institution;
use App\Models\ReportTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

class SelfAssessmentReportGenerator
{
    public function generate(Institution $institution, ?Assessment $institutionalAssessment = null, int $year = null): GeneratedReport
    {
        $year = $year ?? (int) date('Y');
        $template = ReportTemplate::where('type', 'sar')->where('is_active', true)->firstOrFail();

        $institutionalAssessment = $institutionalAssessment ?? Assessment::query()
            ->where('institution_id', $institution->id)
            ->where('assessment_type', 'institutional')
            ->latest()
            ->first();

        $programmeAssessments = Assessment::query()
            ->where('institution_id', $institution->id)
            ->where('assessment_type', 'programme')
            ->with(['programme', 'complianceResult', 'sectionSummaries.section'])
            ->get();

        $institution->load(['profile', 'contact', 'governanceMembers', 'programmes', 'staffMembers']);

        $snapshot = [
            'year' => $year,
            'institution' => $institution->toArray(),
            'profile' => $institution->profile?->toArray(),
            'governance' => $institution->governanceMembers->groupBy('body_type')->toArray(),
            'institutional_assessment' => $institutionalAssessment?->load(['complianceResult', 'sectionSummaries.section'])?->toArray(),
            'programme_assessments' => $programmeAssessments->toArray(),
            'staff_by_programme' => $institution->staffMembers->groupBy('programme_id')->toArray(),
        ];

        $report = GeneratedReport::create([
            'institution_id' => $institution->id,
            'report_template_id' => $template->id,
            'assessment_id' => $institutionalAssessment?->id,
            'reporting_year' => (string) $year,
            'status' => 'generated',
            'generated_by' => auth()->id(),
            'snapshot_data' => $snapshot,
        ]);

        $html = view('reports.sar', compact('snapshot', 'institution'))->render();
        $pdfPath = "reports/sar-{$institution->id}-{$year}-".time().'.pdf';
        Storage::disk('local')->put($pdfPath, Pdf::loadHTML($html)->output());
        $report->file_pdf_path = $pdfPath;

        $docxPath = $this->generateDocx($snapshot, $institution, $year);
        $report->file_docx_path = $docxPath;
        $report->save();

        return $report;
    }

    protected function generateDocx(array $snapshot, Institution $institution, int $year): string
    {
        $phpWord = new PhpWord;
        $section = $phpWord->addSection();
        $section->addTitle('Self-Assessment Report', 1);
        $section->addText($institution->name);
        $section->addText('Reporting Year: '.$year);
        $section->addTextBreak();

        if ($profile = $snapshot['profile'] ?? null) {
            $section->addTitle('Institutional Background', 2);
            $section->addText('Vision: '.($profile['vision'] ?? 'N/A'));
            $section->addText('Mission: '.($profile['mission'] ?? 'N/A'));
        }

        if ($assessment = $snapshot['institutional_assessment'] ?? null) {
            $section->addTitle('Institutional Assessment Scores', 2);
            foreach ($assessment['section_summaries'] ?? [] as $summary) {
                $section->addText(($summary['section']['title'] ?? 'Section').': '.($summary['aggregate_score'] ?? 'N/A'));
            }
        }

        $path = "reports/sar-{$institution->id}-{$year}-".time().'.docx';
        $fullPath = Storage::disk('local')->path($path);
        IOFactory::createWriter($phpWord, 'Word2007')->save($fullPath);

        return $path;
    }
}
