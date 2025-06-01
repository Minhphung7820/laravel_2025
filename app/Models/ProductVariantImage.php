<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return $this->image ? url($this->image) : env('IMAGE_DEFAULT');
    }

    public function stockProduct()
    {
        return $this->belongsTo(StockProduct::class);
    }
}
