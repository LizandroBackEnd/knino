<?php

namespace App\Models;

use App\Models\enums\SpeciesEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Breed extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'species'];

    protected $casts = ['species' => SpeciesEnum::class];

    public function pets() {
        return $this->hasMany(Pet::class);
    }
}
