<?php

namespace App\Models;

use App\Models\enums\SpeciesEnum;
use App\Models\enums\SexEnum;
use App\Models\enums\SizeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Pet extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'birth_date',
        'color',
        'species',
        'sex',
        'photo_url',
        'breed_id',
        'client_id',
        'size',
        'weight'
    ];

    protected $casts = [
        'species' => SpeciesEnum::class,
        'sex' => SexEnum::class,
        'birth_date' => 'date',
        'size' => SizeEnum::class,
        'weight' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function breed()
    {
        return $this->belongsTo(Breed::class);
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtoupper($value);
    }

    public function setWeightAttribute($value)
    {
        $this->attributes['weight'] = $value === null ? null : number_format((float)$value, 2, '.', '');
    }


    public function getPhotoUrlAttribute($value)
    {
        if (empty($value)) {
            return asset('/icons/pets.svg');
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        if (str_starts_with($value, '/storage')) {
            return asset($value);
        }

        return asset(Storage::url($value));
    }
}
