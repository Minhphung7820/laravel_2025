<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockProduct extends Model
{
    protected $fillable = [
        'stock_id',
        'product_id',
        'attribute_first_id',
        'attribute_second_id',
        'quantity',
        'sell_price',
        'purchase_price',
        'product_type',
        'barcode',
        'sku',
        'unit_id',
        'is_sale',
        'is_product_variant',
        'max_discount_percent',
        'max_increase_percent',
        'auto_calc',
        'is_using'
    ];

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeFirst()
    {
        return $this->belongsTo(Attribute::class, 'attribute_first_id');
    }

    public function attributeSecond()
    {
        return $this->belongsTo(Attribute::class, 'attribute_second_id');
    }

    public function variantImages()
    {
        return $this->hasMany(ProductVariantImage::class);
    }
}
