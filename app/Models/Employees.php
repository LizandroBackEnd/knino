<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employees extends Model
{
    use HasFactory;

    /**
     * Use the users table: employees are stored in the users table in this app.
     */
    protected $table = 'users';

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

    /*public function schedules()
    {
        return $this->hasMany(EmployeeSchedule::class);
    }*/

    public function schedules()
    {
        // schedules store user_id that links to this employee's id
        return $this->hasMany(EmployeeSchedule::class, 'user_id');
    }

    /**
     * Convenience: indicate if this user is a veterinarian.
     * Kept similar to App\Models\User::isVeterinarian so other controllers work.
     */
    public function isVeterinarian(): bool
    {
        return strtolower($this->role ?? '') === 'veterinarian';
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'employee_id');
    }
}
