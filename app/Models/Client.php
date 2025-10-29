<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{ 
    use HasFactory;

    protected $fillable = [
        'name',
        'last_name_primary',
        'last_name_secondary',
        'phone',
        'email',
        'address'
    ];

    public function setNameAttribute($value) {
        $this->attributes['name'] = strtoupper($value);
    }
}
