<?php

namespace App\Services\Reports;

use App\Models\Assessment;
use App\Models\GeneratedReport;
use App\Models\Institution;
use App\Models\ReportTemplate;
use App\Services\AssessmentStrengthsAnalysis;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class SelfAssessmentReportGenerator
{
    public function __construct(
        protected AssessmentStrengthsAnalysis $strengthsAnalysis,
        protected SarAssessmentFormatter $sarFormatter,
        protected HtmlReportDocxExporter $docxExporter,
    ) {}

    public function generate(Institution $institution, ?Assessment $institutionalAssessment = null, int $year = null): GeneratedReport
    {
        $year = $year ?? (int) date('Y');
        $template = ReportTemplate::where('type', 'sar')->where('is_active', true)->firstOrFail();

        $institutionalAssessment = $institutionalAssessment ?? Assessment::query()
            ->where('institution_id', $institution->id)
            ->where('assessment_type', 'institutional')
            ->latest()
            ->first();

        $institution->load([
            'profile',
            'contact',
            'governanceMembers',
            'programmes',
            'staffMembers.programme',
            'studentEnrolments.programme',
        ]);

        $programmeAssessments = Assessment::query()
            ->where('institution_id', $institution->id)
            ->where('assessment_type', 'programme')
            ->with(['programme', 'complianceResult', 'sectionSummaries.section', 'responses.criterion'])
            ->get()
            ->map(fn (Assessment $assessment) => $this->formatProgrammeAssessment($assessment, $institution))
            ->values()
            ->all();

        $institutionalAssessment?->load([
            'responses.criterion.section',
            'responses.criterion.rubricLevels',
            'complianceResult',
            'sectionSummaries.section',
        ]);

        $institutionalFormatted = $institutionalAssessment
            ? $this->sarFormatter->formatAssessment($institutionalAssessment, false)
            : null;

        if ($institutionalFormatted) {
            $institutionalFormatted['strengths_improvement_rows'] = $this->sarFormatter->buildStrengthsImprovementRows($institutionalAssessment);
        }

        $summaryRows = $this->buildSummaryTableRows($institution, $institutionalFormatted, $programmeAssessments);

        $snapshot = [
            'year' => $year,
            'institution' => $institution->toArray(),
            'profile' => $institution->profile?->toArray(),
            'contact' => $institution->contact?->toArray(),
            'governance' => $institution->governanceMembers->groupBy('body_type')->toArray(),
            'staff_members' => $institution->staffMembers->load('programme')->toArray(),
            'student_enrolments' => $institution->studentEnrolments->load('programme')->toArray(),
            'institutional_assessment' => $institutionalFormatted,
            'programme_assessments' => $programmeAssessments,
            'summary_table_rows' => $summaryRows,
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

        $docxPath = "reports/sar-{$institution->id}-{$year}-".time().'.docx';
        $report->file_docx_path = $this->docxExporter->store($html, $docxPath);
        $report->save();

        return $report;
    }

    protected function formatProgrammeAssessment(Assessment $assessment, Institution $institution): array
    {
        $formatted = $this->sarFormatter->formatAssessment($assessment, true);
        $formatted['strengths_improvement_rows'] = $this->sarFormatter->buildStrengthsImprovementRows($assessment);
        $formatted['staff'] = $institution->staffMembers
            ->where('programme_id', $assessment->programme_id)
            ->values()
            ->toArray();

        return $formatted;
    }

    protected function buildSummaryTableRows(Institution $institution, ?array $institutional, array $programmeAssessments): array
    {
        $rows = [];

        if ($institutional) {
            $rows[] = [
                'name' => $institution->name,
                'type' => 'institution',
                'aggregate_score' => number_format($institutional['overall_average'], 2),
                'observations' => $this->briefObservations($institutional),
                'recommendation' => $institutional['overall_recommendation'],
            ];
        }

        foreach ($programmeAssessments as $pa) {
            $rows[] = [
                'name' => $pa['programme']['name'] ?? $pa['title'],
                'type' => 'programme',
                'aggregate_score' => number_format($pa['overall_average'], 2),
                'observations' => $this->briefObservations($pa),
                'recommendation' => $pa['overall_recommendation'],
            ];
        }

        return $rows;
    }

    protected function briefObservations(array $assessmentData): string
    {
        $improvements = collect($assessmentData['strengths_improvement_rows'] ?? [])
            ->flatMap(fn ($row) => $row['improvements'] ?? [])
            ->take(3)
            ->implode(' ');

        if ($improvements !== '') {
            return $improvements;
        }

        $strengths = collect($assessmentData['strengths_improvement_rows'] ?? [])
            ->flatMap(fn ($row) => $row['strengths'] ?? [])
            ->take(2)
            ->implode(' ');

        return $strengths ?: 'Assessment completed.';
    }
}
