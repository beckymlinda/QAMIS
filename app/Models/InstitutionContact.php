<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstitutionContact extends Model
{
    use Auditable, BelongsToInstitution;

    protected $fillable = [
        'institution_id', 'postal_address', 'fax', 'email',
        'website', 'telephone', 'mobile', 'location',
    ];

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }
}
