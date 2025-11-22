<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeSchedule extends Model
{
    protected $fillable = [
        'user_id',
        'day_of_week_start',
        'day_of_week_end',
        'start_time',
        'end_time',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function user() 
    {
        return $this->belongsTo(Employees::class, 'user_id');
    }
}