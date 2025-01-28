<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'tennis_court_id',
        'timeslot_id',
        'start_time',
        'end_time',
        'price'
    ];
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function tennisCourt()
    {
        return $this->belongsTo(TennisCourt::class);
    }

    public function timeslot()
    {
        return $this->belongsTo(Timeslot::class);
    }
}
