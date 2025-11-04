<?php

namespace App\Models\enums;

enum SexEnum: string {
    case FEMALE = 'hembra';
    case MALE = 'macho';

    public static function values(): array {
        return array_column(self::cases(), 'value');
    }
}