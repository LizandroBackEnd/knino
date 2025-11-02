<?php

namespace App\Models;

use App\Models\enums\SpeciesEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'birth_date',
        'color',
        'species',
        'photo_url',
        'breed_id',
        'client_id'
    ];

    protected $casts = [
        'species' => SpeciesEnum::class,
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
}
