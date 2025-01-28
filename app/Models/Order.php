<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'customer_name',
        'customer_email',
        'customer_phone',
        'total_amount',
        'status',
        'paid_at',
        'payment_data',
        'payment_order_id'
    ];

    protected $casts = [
        'total_amount' => 'float',
        'paid_at' => 'datetime',
        'payment_data' => 'array',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

}
