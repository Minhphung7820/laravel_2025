<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'type',
    ];

    public function stockProducts()
    {
        return $this->hasMany(StockProduct::class);
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'stock_products')
            ->withPivot(['attribute_first_id', 'attribute_second_id']);
    }
}
