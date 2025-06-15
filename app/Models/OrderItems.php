<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItems extends Model
{
    protected $table = 'order_items';
    protected $fillable = [
        'id',
        'order_id',
        'product_id',
        'stock_id',
        'stock_product_id',
        'attribute_first_id',
        'attribute_second_id',
        'product_type',
        'quantity',
        'sell_price',
        'purchase_price',
        'created_at',
        'updated_at',
        'product_name',
        'image',
        'parent_id',
        'unit_name',
        'sell_price_main',
        'purchase_price_main'
    ];
}
