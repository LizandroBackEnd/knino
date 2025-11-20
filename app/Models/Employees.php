<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employees extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'last_name_primary',
        'last_name_secondary',
        'phone',
        'email',
    ];

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtoupper($value);
    }

    public function setLastNamePrimaryAttribute($value)
    {
        $this->attributes['last_name_primary'] = strtoupper($value);
    }

    public function setLastNameSecondaryAttribute($value)
    {
        $this->attributes['last_name_secondary'] = strtoupper($value);
    }
}
