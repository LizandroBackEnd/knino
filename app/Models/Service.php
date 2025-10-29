<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'photo_url',
    ];

    public function setNameAttribute($value) {
        $this->attributes['name'] = strtoupper($value);
    }
}
