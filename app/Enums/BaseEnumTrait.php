<?php

namespace App\Enums;

trait BaseEnumTrait
{
    public static function names(): array
    {
        return array_map(static fn($item) => $item->name, static::cases());
    }

    public static function values(): array
    {
        return array_map(static fn($item) => $item->value, static::cases());
    }

    public static function titles(): array
    {
        return array_map(static fn($item) => ucwords($item->value), static::cases());
    }
}
