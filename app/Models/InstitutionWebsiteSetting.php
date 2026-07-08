<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InstitutionWebsiteSetting extends Model
{
    protected $fillable = [
        'institution_id',
        'slug',
        'is_published',
        'school_name',
        'tagline',
        'hero_description',
        'hero_features',
        'about_content',
        'team_members',
        'programs_intro',
        'application_intro',
        'application_payment_instructions',
        'application_requirements',
        'application_upload_max_mb',
        'footer_address',
        'footer_phone',
        'footer_email',
        'footer_extra',
        'primary_color',
        'secondary_color',
        'logo_path',
        'slider_images',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'hero_features' => 'array',
            'slider_images' => 'array',
            'team_members' => 'array',
            'application_upload_max_mb' => 'integer',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public static function defaultSlugFor(Institution $institution): string
    {
        $base = Str::slug($institution->acronym ?: $institution->name);
        $slug = $base ?: 'school-'.$institution->id;
        $candidate = $slug;
        $counter = 1;

        while (static::query()->where('slug', $candidate)->where('institution_id', '!=', $institution->id)->exists()) {
            $candidate = $slug.'-'.$counter;
            $counter++;
        }

        return $candidate;
    }

    public static function forInstitution(Institution $institution): self
    {
        return static::query()->firstOrCreate(
            ['institution_id' => $institution->id],
            [
                'slug' => static::defaultSlugFor($institution),
                'school_name' => $institution->name,
            ]
        );
    }

    public function displayName(): string
    {
        return $this->school_name ?: $this->institution->name;
    }

    public function hasLogo(): bool
    {
        return filled($this->logo_path) && Storage::disk('public')->exists($this->logo_path);
    }

    public function logoUrl(): ?string
    {
        return $this->hasLogo() ? Storage::disk('public')->url($this->logo_path) : null;
    }

    /** @return array<int, string> */
    public function sliderUrls(): array
    {
        return collect($this->slider_images ?? [])
            ->filter(fn ($path) => filled($path) && Storage::disk('public')->exists($path))
            ->map(fn ($path) => Storage::disk('public')->url($path))
            ->values()
            ->all();
    }

    /** @return array<int, array{name: string, role: string, photo_url: ?string}> */
    public function teamMembersForDisplay(): array
    {
        return collect($this->team_members ?? [])
            ->filter(fn ($member) => filled($member['name'] ?? null) || filled($member['role'] ?? null))
            ->map(function ($member) {
                $photoPath = $member['photo'] ?? null;
                $photoUrl = ($photoPath && Storage::disk('public')->exists($photoPath))
                    ? Storage::disk('public')->url($photoPath)
                    : null;

                return [
                    'name' => $member['name'] ?? '',
                    'role' => $member['role'] ?? '',
                    'photo_url' => $photoUrl,
                ];
            })
            ->values()
            ->all();
    }

    public function isConfigured(): bool
    {
        return $this->is_published
            || filled($this->hero_description)
            || filled($this->about_content)
            || $this->hasLogo()
            || count($this->sliderUrls()) > 0;
    }

    /** @return array{primary: string, secondary: string, name: string, logo_url: ?string} */
    public function branding(): array
    {
        return [
            'primary' => $this->primary_color ?: '#0f2744',
            'secondary' => $this->secondary_color ?: '#8cc63f',
            'name' => $this->displayName(),
            'logo_url' => $this->logoUrl(),
        ];
    }

    public function defaultApplicationPaymentInstructions(): string
    {
        return "Application Fee: As stated per programme\n\n"
            ."Bank Name: [Configure in Settings]\n"
            ."Account Number: [Configure in Settings]\n"
            ."Account Name: [Configure in Settings]\n"
            ."Mobile Money: [Configure in Settings]\n\n"
            ."Use your full name and programme as payment reference.";
    }

    public function defaultApplicationRequirements(): string
    {
        return "Required uploads:\n"
            ."• National ID or Passport (optional)\n"
            ."• Academic Certificate(s)\n"
            ."• Examination Results\n"
            ."• Passport Photo (optional)\n"
            ."• Proof of Application Fee Payment\n\n"
            ."Accepted formats: PDF, JPG, PNG";
    }

    /** Preserve line breaks as typed; trim edges and normalise line endings only. */
    public function displayText(?string $value): string
    {
        if (! filled($value)) {
            return '';
        }

        return preg_replace("/\r\n|\r/", "\n", trim($value));
    }
}
