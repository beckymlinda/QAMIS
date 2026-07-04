<?php

namespace Tests\Unit;

use App\Services\Reports\HtmlReportDocxExporter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class HtmlReportDocxExporterTest extends TestCase
{
    public function test_exports_report_html_content_to_docx(): void
    {
        Storage::fake('local');

        $html = <<<'HTML'
<!DOCTYPE html>
<html><head><meta charset="utf-8"><style>.page-break{page-break-before:always}</style></head>
<body>
<h1>Self-Assessment Report</h1>
<h2>Executive Summary</h2>
<p>Institution overview narrative.</p>
<div class="page-break"></div>
<h2>Institutional Assessment</h2>
<table><tr><th>Area</th><th>Score</th></tr><tr><td>Governance</td><td>3.50</td></tr></table>
</body></html>
HTML;

        $path = 'reports/test-sar.docx';
        app(HtmlReportDocxExporter::class)->store($html, $path);

        Storage::disk('local')->assertExists($path);
        $this->assertGreaterThan(2000, Storage::disk('local')->size($path));

        $zip = new ZipArchive;
        $fullPath = Storage::disk('local')->path($path);
        $this->assertTrue($zip->open($fullPath) === true);
        $documentXml = $zip->getFromName('word/document.xml');
        $zip->close();

        $this->assertNotFalse($documentXml);
        $this->assertStringContainsString('Executive Summary', $documentXml);
        $this->assertStringContainsString('Institution overview narrative', $documentXml);
        $this->assertStringContainsString('Governance', $documentXml);
    }

    public function test_exports_sar_blade_template_to_docx(): void
    {
        Storage::fake('local');

        $snapshot = [
            'year' => 2026,
            'profile' => [
                'executive_summary' => 'Executive summary text.',
                'abbreviations_acronyms' => 'HEQAMIS — Higher Education Quality Assurance',
                'vision' => 'A leading university',
                'mission' => 'To educate',
                'core_values' => 'Integrity',
            ],
            'summary_table_rows' => [
                [
                    'name' => 'Test University',
                    'aggregate_score' => '3.25',
                    'observations' => 'Strong governance.',
                    'recommendation' => 'Accreditation',
                ],
            ],
            'governance' => [],
            'staff_members' => [],
            'student_enrolments' => [],
            'institutional_assessment' => null,
            'programme_assessments' => [],
        ];

        $institution = (object) ['name' => 'Test University'];
        $html = view('reports.sar', compact('snapshot', 'institution'))->render();

        $path = 'reports/test-sar-blade.docx';
        app(HtmlReportDocxExporter::class)->store($html, $path);

        Storage::disk('local')->assertExists($path);

        $zip = new ZipArchive;
        $documentXml = $zip->open(Storage::disk('local')->path($path)) === true
            ? $zip->getFromName('word/document.xml')
            : false;
        $zip->close();

        $this->assertNotFalse($documentXml);
        $this->assertStringContainsString('Executive summary text.', $documentXml);
        $this->assertStringContainsString('List of Abbreviations and Acronyms', $documentXml);
        $this->assertStringContainsString('Grading and Interpretation of Assessment Scores', $documentXml);
    }

    public function test_applies_logo_centering_and_table_borders_for_word(): void
    {
        Storage::fake('local');

        $html = <<<'HTML'
<!DOCTYPE html>
<html><head><meta charset="utf-8"></head><body>
<div>
<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==" alt="Institution logo" style="height:70px;max-width:220px;display:block;margin-bottom:10px;">
<h1>Test University</h1>
</div>
<table><tr><th>Area</th><th>Score</th></tr><tr><td>Governance</td><td>3.50</td></tr></table>
</body></html>
HTML;

        $path = 'reports/test-formatting.docx';
        app(HtmlReportDocxExporter::class)->store($html, $path);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open(Storage::disk('local')->path($path)) === true);
        $documentXml = $zip->getFromName('word/document.xml');
        $zip->close();

        $this->assertNotFalse($documentXml);
        $this->assertStringContainsString('w:jc w:val="center"', $documentXml);
        $this->assertStringContainsString('w:tblBorders', $documentXml);
        $this->assertStringContainsString('w:tblCellMar', $documentXml);
        $this->assertStringContainsString('w:pgMar w:top="1800"', $documentXml);
        $this->assertStringContainsString('Governance', $documentXml);
    }
}
