<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'institution_id', 'name', 'email', 'password', 'is_active', 'last_login_at', 'profile_photo_path',
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

    public function isApplicant(): bool
    {
        return $this->hasRole('applicant');
    }

    public function isGuestInstitution(): bool
    {
        return $this->hasRole('guest_institution');
    }

    public function homeRoute(): string
    {
        if ($this->isApplicant()) {
            return route('applicant.dashboard');
        }

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

    public function initials(): string
    {
        $initials = collect(explode(' ', $this->name))
            ->filter()
            ->take(2)
            ->map(fn (string $part) => strtoupper(substr($part, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : '?';
    }

    public function hasProfilePhoto(): bool
    {
        return filled($this->profile_photo_path)
            && Storage::disk('public')->exists($this->profile_photo_path);
    }

    public function profilePhotoUrl(): ?string
    {
        if (! $this->hasProfilePhoto()) {
            return null;
        }

        return Storage::disk('public')->url($this->profile_photo_path);
    }

    public function deleteProfilePhoto(): void
    {
        if ($this->profile_photo_path && Storage::disk('public')->exists($this->profile_photo_path)) {
            Storage::disk('public')->delete($this->profile_photo_path);
        }

        $this->forceFill(['profile_photo_path' => null])->save();
    }
}
