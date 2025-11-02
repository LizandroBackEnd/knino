<?php

namespace App\Models\enums;

enum SpeciesEnum: string {
    case DOG = 'perro';
    case CAT = "gato";

    public static function values(): array {
        return array_column(self::cases(), 'value');
    }
}