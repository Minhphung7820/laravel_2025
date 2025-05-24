<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductVariantImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_product_id',
        'image',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return $this->image ? url($this->image) : null;
    }

    public function stockProduct()
    {
        return $this->belongsTo(StockProduct::class);
    }
}
