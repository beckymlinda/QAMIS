<?php

namespace App\Support;

use App\Models\ProgrammeApplication;

class ApplicationDocuments
{
    /** @return array<string, array{column: string, label: string, required: bool, accept: string}> */
    public static function types(): array
    {
        return [
            'certificates' => [
                'column' => 'certificates_path',
                'label' => 'Academic certificates',
                'required' => true,
                'accept' => '.pdf,.jpg,.jpeg,.png',
            ],
            'results' => [
                'column' => 'results_path',
                'label' => 'Examination results',
                'required' => true,
                'accept' => '.pdf,.jpg,.jpeg,.png',
            ],
            'payment_proof' => [
                'column' => 'payment_proof_path',
                'label' => 'Proof of application fee',
                'required' => true,
                'accept' => '.pdf,.jpg,.jpeg,.png',
            ],
            'id_document' => [
                'column' => 'id_document_path',
                'label' => 'National ID / Passport',
                'required' => false,
                'accept' => '.pdf,.jpg,.jpeg,.png',
            ],
            'photo' => [
                'column' => 'photo_path',
                'label' => 'Passport photo',
                'required' => false,
                'accept' => '.jpg,.jpeg,.png',
            ],
        ];
    }

    public static function columnFor(string $field): ?string
    {
        return self::types()[$field]['column'] ?? null;
    }

    public static function labelFor(string $field): string
    {
        return self::types()[$field]['label'] ?? ucfirst(str_replace('_', ' ', $field));
    }

    /** @return list<string> */
    public static function requiredFields(): array
    {
        return collect(self::types())
            ->filter(fn ($meta) => $meta['required'])
            ->keys()
            ->all();
    }

    public static function pathFor(ProgrammeApplication $application, string $field): ?string
    {
        $column = self::columnFor($field);

        return $column ? $application->{$column} : null;
    }

    public static function isPreviewable(?string $path): bool
    {
        if (! $path) {
            return false;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp'], true);
    }

    public static function isImage(?string $path): bool
    {
        if (! $path) {
            return false;
        }

        return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
    }
}
