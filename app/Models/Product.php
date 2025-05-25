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

    public function attributes()
    {
        return $this->hasMany(StockProduct::class)
            ->where('product_type', 'variable');
    }

    public function galleryImages()
    {
        return $this->hasMany(ProductImage::class);
    }
}
