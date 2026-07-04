<?php

namespace App\Services\Reports;

use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\Element\AbstractContainer;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\SimpleType\TblWidth;

class HtmlReportDocxExporter
{
    /** Page margins in twips (~1.25 inch). */
    private const SECTION_MARGIN = 1800;

    /** Table cell padding in twips (~8pt). */
    private const TABLE_CELL_MARGIN = 160;

    private const TABLE_STYLE_NAME = 'qamisReportTable';

    public function store(string $html, string $relativePath): string
    {
        $phpWord = new PhpWord;
        $this->registerTableStyle($phpWord);

        $section = $phpWord->addSection([
            'marginTop' => self::SECTION_MARGIN,
            'marginRight' => self::SECTION_MARGIN,
            'marginBottom' => self::SECTION_MARGIN,
            'marginLeft' => self::SECTION_MARGIN,
        ]);

        Html::addHtml($section, $this->prepareHtml($html), true, true);
        $this->applyTableLayout($section);

        $fullPath = Storage::disk('local')->path($relativePath);
        $directory = dirname($fullPath);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        IOFactory::createWriter($phpWord, 'Word2007')->save($fullPath);

        return $relativePath;
    }

    protected function registerTableStyle(PhpWord $phpWord): void
    {
        $phpWord->addTableStyle(self::TABLE_STYLE_NAME, [
            'borderSize' => 6,
            'borderColor' => '999999',
            'cellMargin' => self::TABLE_CELL_MARGIN,
            'width' => 100 * 50,
            'unit' => TblWidth::PERCENT,
        ], [
            'bgColor' => 'e8eef4',
        ]);
    }

    protected function applyTableLayout(AbstractContainer $container): void
    {
        foreach ($container->getElements() as $element) {
            if ($element instanceof Table) {
                $style = $element->getStyle();
                $style->setCellMargin(self::TABLE_CELL_MARGIN);

                if (! $style->hasBorder()) {
                    $style->setBorderSize(6);
                    $style->setBorderColor('999999');
                }
            }

            if ($element instanceof AbstractContainer) {
                $this->applyTableLayout($element);
            }
        }
    }

    protected function prepareHtml(string $html): string
    {
        $html = preg_replace(
            '/<div[^>]*class="[^"]*page-break[^"]*"[^>]*>\s*<\/div>/i',
            '<p style="page-break-after: always;"></p>',
            $html
        );

        $html = preg_replace('/<(meta|br|hr|img)([^>]*?)(?<!\/)>/i', '<$1$2/>', $html);
        $html = $this->injectWordStyles($html);
        $html = $this->enhanceLogo($html);
        $html = $this->enhanceTables($html);

        return $html;
    }

    protected function injectWordStyles(string $html): string
    {
        $wordStyles = <<<'CSS'

/* Word export layout */
body{font-family:Calibri,Arial,sans-serif;font-size:11pt;line-height:1.45;color:#111111}
h1{font-size:18pt;color:#0f2744;text-align:center;margin:0 0 10pt;text-transform:uppercase}
h2{font-size:14pt;color:#0f2744;margin:20pt 0 10pt;border-bottom:1px solid #0f2744;padding-bottom:5pt}
h3{font-size:12pt;color:#0f2744;margin:14pt 0 8pt}
h4{font-size:11pt;color:#333333;margin:12pt 0 6pt;font-weight:bold}
p{margin:8pt 0}
pre{margin:8pt 0;white-space:pre-wrap}
table{width:100%;border-collapse:collapse;margin:14pt 0;border:1px #999999 solid}
th,td{border:1px #999999 solid;padding:8pt;vertical-align:top;text-align:left}
th{background-color:#e8eef4;color:#0f2744;font-weight:bold}
.table-caption{font-size:10pt;font-weight:bold;color:#0f2744;margin:12pt 0 6pt}
.muted{color:#666666;font-style:italic}
.center{text-align:center}
.bullet-list{margin:6pt 0 6pt 18pt}
.bullet-list li{margin:4pt 0}
.aggregate-row td{font-weight:bold;background-color:#f3f4f6}
CSS;

        if (preg_match('/<\/style>/i', $html)) {
            return preg_replace('/<\/style>/i', $wordStyles.'</style>', $html, 1);
        }

        return preg_replace('/<head>/i', '<head><style>'.$wordStyles.'</style>', $html, 1);
    }

    protected function enhanceLogo(string $html): string
    {
        return preg_replace_callback(
            '/<img([^>]*alt="Institution logo"[^>]*)\/>/i',
            function (array $matches): string {
                $attributes = $matches[1];

                $attributes = preg_replace('/\sstyle="[^"]*"/i', '', $attributes);
                $attributes = preg_replace('/\s(height|width)="[^"]*"/i', '', $attributes);

                return '<p style="text-align:center; margin:0 0 14pt;">'
                    .'<img'.$attributes.' width="160" height="55" style="width:160px;height:55px;"/>'
                    .'</p>';
            },
            $html
        );
    }

    protected function enhanceTables(string $html): string
    {
        $tableStyle = 'width:100%;border-collapse:collapse;margin:14pt 0;border:1px #999999 solid;';
        $headerCellStyle = 'border:1px #999999 solid;padding:8pt;vertical-align:top;background-color:#e8eef4;color:#0f2744;font-weight:bold;';
        $bodyCellStyle = 'border:1px #999999 solid;padding:8pt;vertical-align:top;';

        $html = preg_replace_callback(
            '/<table\b([^>]*)>/i',
            function (array $matches) use ($tableStyle): string {
                $attributes = $this->addTagClass($matches[1], self::TABLE_STYLE_NAME);

                return '<table border="1"'.$this->mergeTagStyle($attributes, $tableStyle).'>';
            },
            $html
        );

        $html = preg_replace_callback(
            '/<th\b([^>]*)>/i',
            fn (array $matches) => '<th'.$this->mergeTagStyle($matches[1], $headerCellStyle).'>',
            $html
        );

        $html = preg_replace_callback(
            '/<td\b([^>]*)>/i',
            fn (array $matches) => '<td'.$this->mergeTagStyle($matches[1], $bodyCellStyle).'>',
            $html
        );

        return $html;
    }

    protected function mergeTagStyle(string $attributes, string $style): string
    {
        if (preg_match('/\sstyle="([^"]*)"/i', $attributes, $matches)) {
            $merged = rtrim($matches[1], '; ').';'.$style;
            $attributes = preg_replace(
                '/\sstyle="[^"]*"/i',
                ' style="'.htmlspecialchars($merged, ENT_QUOTES, 'UTF-8').'"',
                $attributes
            );
        } else {
            $attributes .= ' style="'.htmlspecialchars($style, ENT_QUOTES, 'UTF-8').'"';
        }

        return $attributes;
    }

    protected function addTagClass(string $attributes, string $className): string
    {
        if (preg_match('/\sclass="([^"]*)"/i', $attributes, $matches)) {
            $classes = trim($matches[1].' '.$className);
            $attributes = preg_replace(
                '/\sclass="[^"]*"/i',
                ' class="'.htmlspecialchars($classes, ENT_QUOTES, 'UTF-8').'"',
                $attributes
            );
        } else {
            $attributes .= ' class="'.htmlspecialchars($className, ENT_QUOTES, 'UTF-8').'"';
        }

        return $attributes;
    }
}
