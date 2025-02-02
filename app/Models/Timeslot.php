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

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function tennisCourt()
    {
        return $this->belongsTo(TennisCourt::class);
    }
}
