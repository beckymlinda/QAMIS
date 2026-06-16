<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportTemplate extends Model
{
    protected $fillable = ['type', 'name', 'version', 'section_schema', 'is_active'];

    protected function casts(): array
    {
        return [
            'section_schema' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
