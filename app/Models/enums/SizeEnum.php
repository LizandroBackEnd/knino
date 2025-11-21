<?php

namespace App\Models\enums;

enum SizeEnum: string {
    case MINIATURE = "miniatura";
    case SMALL = "chico";
    case MEDIUM = "mediano";
    case LARGE = "grande";
    case GIANT = "gigante";

    public static function values(): array {
        return array_column(self::cases(), 'value');
    }
}