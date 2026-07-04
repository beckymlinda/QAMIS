<?php

namespace App\Services;

use Illuminate\Support\Str;

class AccreditationToolParser
{
    /**
     * @return array<string, array<int, array{title: string, mandatory: bool}>>
     */
    public function parse(string $path): array
    {
        if (! is_readable($path)) {
            return [];
        }

        $content = file_get_contents($path);
        $content = preg_replace('/<br\s*\/?>/i', ' ', $content) ?? $content;
        $content = strip_tags($content);

        return [
            'AREA-5.1' => $this->parseNumberedBlock($content, '5.1.1 Rating on availability and implementation Physical infrastructure', '5.2 Rating on availability and implementation of library'),
            'AREA-5.2' => $this->parseNumberedBlock($content, '5.2 Rating on availability and implementation of library and Learning Resources Centre', '5.2 Rating on ICT infrastructure'),
            'AREA-5.2-ICT' => $this->parseNumberedBlock($content, '5.2 Rating on ICT infrastructure and e-learning resources', '5.3.2 A rating on availability and implementation of electronic student management'),
            'AREA-4.1' => $this->parseNumberedBlock($content, '4.1 Rating on availability and implementation on Management of Finance', '4.2. Rating on availability and implementation on institutional equipment'),
            'AREA-4.2' => $this->parseNumberedBlock($content, '4.2. Rating on availability and implementation on institutional equipment and Transport', 'AREA 5:'),
            'AREA-6' => $this->parseNumberedBlock($content, 'Rating on availability and Implementation of Student Support Services', 'AREA 7: TEACHING'),
            'AREA-7' => $this->parseNumberedBlock($content, 'Rating on the availability and implementation of teaching and learning strategy', 'AREA 8: RESEARCH'),
            'AREA-8' => $this->parseNumberedBlock($content, 'Rating on availability and implementation research and innovation', 'AREA 9. COMMUNITY OUTREACH'),
            'AREA-9' => $this->parseNumberedBlock($content, 'Rating on availability and implementation of community outreach/industry engagement', 'OVERALL QUALITY RATING'),
        ];
    }

    /**
     * Sections derived from NCHE minimum standards where the accreditation tool
     * does not define a separate rating table (aligned with institutional template).
     *
     * @return array<int, array{title: string, mandatory: bool}>
     */
    public function waterAndSanitationCriteria(): array
    {
        return $this->asCriteria([
            'Adequate, safe and clean water supply',
            'Water reservoirs capable of meeting 24 hours demand',
            'Wastewater collection, treatment and disposal system',
            'Evidence of approval by relevant local authority for water and effluent disposal',
            'Surface water drained and disposed without public nuisance',
            'Master plan illustrating waste, soil drain pipes, sewers and storm water drains',
            'Clean and hygienic water closets, urinals and wash hand basins',
            'Adequate toilet provision ratios for male and female students',
            'Adequate bath or shower facilities for male and female students',
            'Sanitary disposal facilities provided and maintained',
        ]);
    }

    /**
     * @return array<int, array{title: string, mandatory: bool}>
     */
    public function classroomAndLaboratoryCriteria(): array
    {
        return $this->asCriteria([
            'Classrooms and lecture theatres are well ventilated with adequate natural and artificial lighting',
            'Classrooms and lecture theatres are easily accessible to everyone',
            'Sufficient lecturing spaces to accommodate student numbers taking mode of delivery into account',
            'Sciences laboratories are functional with requisite equipment',
            'Sufficient laboratory facilities to accommodate students in science programmes',
            'Laboratory equipment is up to date and well maintained',
        ], ['Sufficient lecturing spaces to accommodate student numbers taking mode of delivery into account']);
    }

    /**
     * @return array<int, array{title: string, mandatory: bool}>
     */
    public function qualityAssuranceCriteria(): array
    {
        return $this->asCriteria([
            'Functional internal quality assurance system',
            'Primary responsibility for quality of programmes and courses',
            'Regular curriculum review at mandatory programme cycle intervals',
            'Programmes audited by NCHE at least once every three years',
            'Audit recommendations implemented',
            'Adequate financial resources committed to institutional mission and values',
            'Dedicated funds for research and publication (minimum 1% of operating budget)',
            'Strong link between research and teaching',
            'Course scheduling appropriate to educational delivery and student needs',
            'Effective mechanisms to quality assure processing and security of certificates',
        ], ['Functional internal quality assurance system']);
    }

    /**
     * @param  array<int, string>  $titles
     * @param  array<int, string>  $mandatoryTitles
     * @return array<int, array{title: string, mandatory: bool}>
     */
    protected function asCriteria(array $titles, array $mandatoryTitles = []): array
    {
        return collect($titles)->map(fn (string $title) => [
            'title' => $title,
            'mandatory' => in_array($title, $mandatoryTitles, true) || str_starts_with($title, '*'),
        ])->values()->all();
    }

    /**
     * @return array<int, array{title: string, mandatory: bool}>
     */
    protected function parseNumberedBlock(string $content, string $startNeedle, string $endNeedle): array
    {
        $start = stripos($content, $startNeedle);
        if ($start === false) {
            return [];
        }

        $end = stripos($content, $endNeedle, $start + strlen($startNeedle));
        $block = $end === false
            ? substr($content, $start)
            : substr($content, $start, $end - $start);

        $criteria = [];

        if (preg_match_all('/\|(\d+)\.?\s*\|([^|]+?)\|/s', $block, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $sequence = (int) $match[1];
                $title = $this->cleanText($match[2]);

                if ($title === '' || $this->isNoiseRow($title)) {
                    continue;
                }

                $mandatory = str_contains($match[2], '*') || str_contains($title, '*');
                $title = ltrim($title, '* ');

                $criteria[$sequence] = [
                    'title' => $title,
                    'mandatory' => $mandatory,
                ];
            }
        }

        if ($criteria === [] && preg_match_all('/\|(\d+)\|([^|]+?)\|/s', $block, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $sequence = (int) $match[1];
                $title = $this->cleanText($match[2]);

                if ($title === '' || $this->isNoiseRow($title)) {
                    continue;
                }

                $mandatory = str_contains($match[2], '*');
                $title = ltrim($title, '* ');

                $criteria[$sequence] = [
                    'title' => $title,
                    'mandatory' => $mandatory,
                ];
            }
        }

        ksort($criteria);

        $criteria = $this->appendInfrastructureTailItems($criteria, $block);

        return array_values($criteria);
    }

    /**
     * @param  array<int, array{title: string, mandatory: bool}>  $criteria
     * @return array<int, array{title: string, mandatory: bool}>
     */
    protected function appendInfrastructureTailItems(array $criteria, string $block): array
    {
        if (count($criteria) >= 21 || stripos($block, 'firefighting') === false) {
            return $criteria;
        }

        $tail = [
            19 => 'Adequate and well serviced firefighting equipment',
            20 => 'Fire drills conducted periodically for staff and students',
            21 => 'Assembling area and fire exit procedures are available and known to all',
        ];

        foreach ($tail as $sequence => $title) {
            if (! isset($criteria[$sequence])) {
                $criteria[$sequence] = ['title' => $title, 'mandatory' => false];
            }
        }

        ksort($criteria);

        return $criteria;
    }

    protected function isNoiseRow(string $title): bool
    {
        $normalized = Str::lower($title);

        $noise = [
            'rating on the basis',
            'assessment value',
            'total assessment',
            'aggregate value',
            'sn',
            'area of assessment',
            'service type',
            'assessed areas',
            'research and innovation areas',
            'standards',
            'areas on finances',
            'area',
            'comments',
            'remarks',
            'score',
            'comment',
        ];

        foreach ($noise as $needle) {
            if (str_starts_with($normalized, $needle)) {
                return true;
            }
        }

        return strlen($title) < 8;
    }

    protected function cleanText(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\*\*/', '', $text) ?? $text;
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        return app(RubricMarkdownParser::class)->repairBrokenWords(trim($text));
    }
}
