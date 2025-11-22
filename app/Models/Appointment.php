<?php

namespace App\Models;

use App\Models\enums\SizeEnum;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'pet_id',
        'client_id',
        'service_id',
        'employee_id',
        'size',
        'price',
        'scheduled_at',
        'notes',
        'status',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'price' => 'decimal:2',
        'size' => SizeEnum::class,
    ];

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    public function employee()
    {
        return $this->belongsTo(Employees::class, 'employee_id');
    }
}
