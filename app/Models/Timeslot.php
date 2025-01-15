<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timeslot extends Model
{
    protected $fillable = [
        'tennis_court_id',
        'start_time',
        'end_time',
    ];

    public function tennisCourt()
    {
        return $this->belongsTo(TennisCourt::class);
    }
}
