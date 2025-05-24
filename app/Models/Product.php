<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'type',
        'sku',
        'barcode',
        'have_variant',
        'json_variants',
        'status',
        'have_serial',
        'image_cover',
        'unit_id',
        'category_id',
        'brand_id',
        'supplier_id',
        'warranty'
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

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
}
