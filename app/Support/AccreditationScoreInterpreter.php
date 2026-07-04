<?php

namespace App\Support;

class AccreditationScoreInterpreter
{
    /**
     * Table 8 band label (Not satisfactory / Satisfactory / Excellent).
     */
    public static function bandLabel(float $score): string
    {
        if ($score < 2) {
            return 'Not satisfactory';
        }

        if ($score < 3) {
            return 'Satisfactory';
        }

        return 'Excellent';
    }

    /**
     * Table 8 accreditation outcome text.
     */
    public static function bandOutcome(float $score): string
    {
        if ($score < 2) {
            return 'Not accredited / Withdrawal of Accreditation.';
        }

        if ($score < 3) {
            return 'Accredited with conditions (provided all starred items have a minimum score of 3).';
        }

        return 'Accreditation';
    }

    /**
     * Table 19 / programme score table recommendation column.
     */
    public static function scoreRecommendation(float $score, bool $criticalAreaFailed = false, bool $isProgramme = false): string
    {
        if ($score < 2 || $criticalAreaFailed) {
            return $isProgramme
                ? 'Does not meet the criteria for accreditation and has substantial areas requiring improvement that must be addressed before the NCHE assessment.'
                : 'Does not meet the criteria for accreditation and has substantial areas requiring improvement that must be addressed before the NCHE assessment.';
        }

        if ($score < 3) {
            return $isProgramme
                ? 'Meets the criteria for accreditation but has substantial areas requiring improvement that must be addressed before the NCHE assessment.'
                : 'Meets criteria for accreditation but has areas that need to be improved.';
        }

        return $isProgramme
            ? 'Meets criteria for accreditation.'
            : 'Meets criteria for accreditation.';
    }

    /**
     * Comment on critical (mandatory) areas for a section row.
     */
    public static function criticalAreasComment(bool $hasMandatoryCriteria, bool $criticalFailed): string
    {
        if (! $hasMandatoryCriteria) {
            return 'No critical area';
        }

        return $criticalFailed ? 'Critical area failed' : 'Passed all critical areas';
    }
}
