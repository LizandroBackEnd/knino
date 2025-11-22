<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'photo_url',
        'price_by_size',
    ];

    protected $casts = [
        'price_by_size' => 'array',
    ];

    public function setNameAttribute($value) {
        $this->attributes['name'] = strtoupper($value);
    }


    public function getPhotoUrlAttribute($value)
    {
        if (empty($value)) {
            return asset('/icons/services.svg');
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        if (str_starts_with($value, '/storage')) {
            return asset($value);
        }


        return asset(Storage::url($value));
    }

    public function getPriceForSize(?string $size): float {
        if ($size ===  null) {
            throw new InvalidArgumentException("Tamaño no especificado");
        }
        if (!is_array($this->price_by_size) || !isset($this->price_by_size[$size])) {
            throw new InvalidArgumentException("Precio no definido para el tamaño");
        }
        return (float) $this->price_by_size[$size];
    }
}
