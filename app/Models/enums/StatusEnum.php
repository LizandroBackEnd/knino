<?php

namespace App\Models\enums;

enum StatusEnum: string {
    case SCHEDULED = "programada";
    case REPROGRAMADA = "reprogramada";
    case CANCELADA = "cancelada";
    case EXPIRED = "expirada";
    case COMPLETED = "completada";

    public static function values(): array {
        return array_column(self::cases(), 'value');
    }
}