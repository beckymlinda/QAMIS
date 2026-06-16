<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneratedReport extends Model
{
    use Auditable, BelongsToInstitution;

    protected $fillable = [
        'institution_id', 'report_template_id', 'assessment_id', 'reporting_year',
        'status', 'generated_by', 'file_pdf_path', 'file_docx_path', 'snapshot_data',
    ];

    protected function casts(): array
    {
        return ['snapshot_data' => 'array'];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ReportTemplate::class, 'report_template_id');
    }
}
