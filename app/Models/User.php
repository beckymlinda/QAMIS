<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'institution_id', 'name', 'email', 'password', 'is_active', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function studentProfile(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function staffProfile(): HasOne
    {
        return $this->hasOne(StaffMember::class);
    }

    public function isStudent(): bool
    {
        return $this->hasRole('student');
    }

    public function isLecturer(): bool
    {
        return $this->hasRole('lecturer');
    }

    public function homeRoute(): string
    {
        if ($this->isStudent()) {
            return route('student.dashboard');
        }

        if ($this->isLecturer()) {
            return route('lecturer.dashboard');
        }

        return route('dashboard');
    }

    public function isNcheOrSystemAdmin(): bool
    {
        return $this->hasAnyRole(['system_admin', 'nche_admin']);
    }
}
