<?php

namespace App\Models;

use App\Models\enums\SpeciesEnum;
use App\Models\enums\SexEnum;
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
        'client_id'
    ];

    protected $casts = [
        'species' => SpeciesEnum::class,
        'sex' => SexEnum::class,
        'birth_date' => 'date'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function breed()
    {
        return $this->belongsTo(Breed::class);
    }

    public function setNameAttribute($value) {
        $this->attributes['name'] = strtoupper($value);
    }

    /**
     * Accessor: return a full public URL for photo_url.
     * We store the relative disk path in the DB (e.g. "images/pets/xxx.png").
     */
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
