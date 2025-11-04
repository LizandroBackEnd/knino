<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    /**
     * Accessor: return a full public URL for photo_url.
     * We store the relative disk path in the DB (e.g. "images/services/xxx.png").
     */
    public function getPhotoUrlAttribute($value)
    {
        if (empty($value)) {
            return asset('/icons/services.svg');
        }

        // If the DB already contains a full URL (e.g. from older records), return it as-is.
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        // If the value already starts with "/storage" convert it to a full asset URL.
        if (str_starts_with($value, '/storage')) {
            return asset($value);
        }

        // Otherwise treat it as a relative disk path (e.g. "images/services/x.png")
        // and let Storage::url build the "/storage/..." path which asset() will turn
        // into a full URL.
        return asset(Storage::url($value));
    }
}
