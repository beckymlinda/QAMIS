<?php

namespace App\Support;

class InstitutionContext
{
    protected static ?int $institutionId = null;

    public static function set(?int $institutionId): void
    {
        static::$institutionId = $institutionId;
    }

    public static function id(): ?int
    {
        return static::$institutionId;
    }

    public static function clear(): void
    {
        static::$institutionId = null;
    }
}
