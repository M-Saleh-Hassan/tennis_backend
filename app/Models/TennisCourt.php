<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TennisCourt extends Model
{
    protected $fillable = [
        'name',
        'description',
        'location',
        'price'
    ];

    public function timeslots()
    {
        return $this->hasMany(Timeslot::class);
    }
}
