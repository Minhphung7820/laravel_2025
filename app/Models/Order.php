<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $fillable = [
        'id',
        'code',
        'transaction_type',
        'transaction_date',
        'customer_id',
        'status',
        'created_at',
        'updated_at',
        'total',
        'final_total',
        'discount_type',
        'discount',
        'phone',
        'email',
        'date',
        'created_by',
        'note',
        'tax_type',
        'tax',
        'shipping_type',
        'shipping_fee',
        'shipping_address',
        'payment_status',
        'shipping_status',
        'parent_id'
    ];
}
