<?php

namespace App\Services;

class RubricMarkdownParser
{
    /**
     * @return array<int, array{title: string, criteria: array<int, array{sequence: int, title: string, levels: array<int, array{label: string, descriptor: string}>}>}>
     */
    public function parse(string $path): array
    {
        if (! is_readable($path)) {
            return [];
        }

        $content = file_get_contents($path);
        $content = preg_replace('/<br\s*\/?>/i', ' ', $content) ?? $content;
        $content = preg_replace('/Page \*\*\d+\*\* of \*\*\d+\*\*/', '', $content) ?? $content;
        $content = preg_replace('/\*\*(\d+\.\d+ Rating on.+?)\*\*/s', '## **$1**', $content) ?? $content;
        $content = preg_replace('/\*\*(\d+\. Rating on.+?)\*\*/s', '## **$1**', $content) ?? $content;
        $content = strip_tags($content);

        if (! preg_match_all('/## \*\*(.+?)\*\*/s', $content, $headers)) {
            return [];
        }

        $sectionKeys = array_values(array_filter(
            array_map(fn ($header) => $this->cleanText($header), $headers[1]),
            fn ($header) => $this->isRubricSectionHeader($header)
        ));

        if ($sectionKeys === []) {
            return [];
        }

        $sections = $this->splitByHeaders($content, $sectionKeys);

        return array_values(array_filter($sections, fn ($section) => count($section['criteria']) > 0));
    }

    /**
     * @param  array<int, string>  $sectionTitles
     * @return array<int, array{title: string, criteria: array<int, array{sequence: int, title: string, levels: array<int, array{label: string, descriptor: string}>}>}>
     */
    protected function splitByHeaders(string $content, array $sectionTitles): array
    {
        $sections = [];

        foreach ($sectionTitles as $index => $title) {
            $startPattern = preg_quote('## **'.$title.'**', '/');
            $endPattern = isset($sectionTitles[$index + 1])
                ? preg_quote('## **'.$sectionTitles[$index + 1].'**', '/')
                : '$';

            if (! preg_match('/'.$startPattern.'(.*?)'.$endPattern.'/s', $content, $match)) {
                continue;
            }

            $block = preg_replace('/\s+/', ' ', $match[1]) ?? $match[1];
            $criteria = [];

            if (preg_match_all('/\|\s*\*\*(\d+)\.([^|]+?)\|([^|]+)\|([^|]+)\|([^|]+)\|([^|]+)\|([^|]+)\|/', $block, $rows, PREG_SET_ORDER)) {
                foreach ($rows as $row) {
                    $sequence = (int) $row[1];
                    $criteria[$sequence] = [
                        'sequence' => $sequence,
                        'title' => $this->cleanText(preg_replace('/\*\*/', '', $row[2]) ?? $row[2]),
                        'levels' => $this->buildLevels([
                            4 => $row[3],
                            3 => $row[4],
                            2 => $row[5],
                            1 => $row[6],
                            0 => $row[7],
                        ]),
                    ];
                }
            }

            if ($criteria !== []) {
                $sections[] = [
                    'title' => $this->cleanText($title),
                    'criteria' => $criteria,
                ];
            }
        }

        return $sections;
    }

    /**
     * @param  array<int, string>  $rawLevels
     * @return array<int, array{label: string, descriptor: string}>
     */
    protected function buildLevels(array $rawLevels): array
    {
        $labels = [
            4 => 'Excellent',
            3 => 'Good',
            2 => 'Satisfactory',
            1 => 'Insufficient',
            0 => 'Poor/Unavailable',
        ];

        $levels = [];

        foreach ($rawLevels as $score => $raw) {
            $descriptor = $this->cleanText($raw);
            if ($descriptor === '') {
                continue;
            }

            $label = $labels[$score];
            if (preg_match('/^(\d+)\s*[–-]\s*([^:]+):\s*(.+)$/', $descriptor, $parts)) {
                $label = $this->cleanText($parts[2]);
                $descriptor = $this->cleanText($parts[3]);
            } elseif (preg_match('/^(\d+)\s*[–-]\s*(.+)$/', $descriptor, $parts)) {
                $descriptor = $this->cleanText($parts[2]);
            }

            $levels[$score] = [
                'label' => $label,
                'descriptor' => $descriptor,
            ];
        }

        return $levels;
    }

    protected function isRubricSectionHeader(string $title): bool
    {
        $normalized = strtolower(trim($title));

        $ignored = [
            'scoring rubric',
            'for',
            'institution accreditation tool',
            'scoring scale',
            'score',
            'description',
            'guidance for mandatory items',
            'criterion',
        ];

        if (in_array($normalized, $ignored, true)) {
            return false;
        }

        if (preg_match('/^area \d+:/', $normalized)) {
            return str_contains($normalized, 'programme');
        }

        return (bool) preg_match('/\d+\.\d+\s+rating/', $normalized)
            || (bool) preg_match('/^\d+\.\s+rating/', $normalized);
    }

    protected function cleanText(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\*\*/', '', $text) ?? $text;
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        return $this->repairBrokenWords(trim($text));
    }

    public function repairBrokenWords(string $text): string
    {
        $brokenSuffixes = 'd|ed|es|ly|ion|ment|nce|ing|ity|al|ed|ness|ance|ence|ism|ive|ous|ful|less|able|ible|ally|fully|ted|ter|tor|try|ted';

        $text = preg_replace('/\b([a-z]{5,}) ('.$brokenSuffixes.')\b/i', '$1$2', $text) ?? $text;
        $text = preg_replace('/\b([a-z]{5,}) ('.$brokenSuffixes.')([a-z]{1,4})\b/i', '$1$2$3', $text) ?? $text;

        $replacements = [
            'Unavaila ble' => 'Unavailable',
            'Insufficien t' => 'Insufficient',
            'Satisfactor y' => 'Satisfactory',
            'Sustainabilit y' => 'Sustainability',
            'Diversificati on' => 'Diversification',
            'communicatio n' => 'communication',
            'underutilize d' => 'underutilized',
            'employabilit y' => 'employability',
            'responsibiliti es' => 'responsibilities',
            'representatio n' => 'representation',
            'representati on' => 'representation',
            'accommodati on' => 'accommodation',
            'Accommodat ion' => 'Accommodation',
            'Disseminat ion' => 'Dissemination',
            'disseminati on' => 'dissemination',
            'performan ce' => 'performance',
            'effectivene ss' => 'effectiveness',
            'pedagogica l' => 'pedagogical',
            'opportuniti es' => 'opportunities',
            'productivit y' => 'productivity',
            'operationa l' => 'operational',
            'Governanc e' => 'Governance',
            'Postgradu ate' => 'Postgraduate',
            'Understandi ng' => 'Understanding',
            'Memoranda of Understandi ng' => 'Memoranda of Understanding',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }
}
