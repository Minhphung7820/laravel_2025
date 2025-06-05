<?php

namespace App\Models;

use App\Constants\Common;
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
        'has_serial',
        'image_cover',
        'unit_id',
        'category_id',
        'brand_id',
        'supplier_id',
        'warranty',
        'description'
    ];

    protected $appends = [
        'status_text',
        'type_text',
        'image_cover_url'
    ];

    public function stockData()
    {
        return $this->hasMany(StockProduct::class, 'product_id')->where('product_type', 'root_stock');
    }

    public function getImageCoverUrlAttribute()
    {
        return $this->image_cover ? url($this->image_cover) : env('IMAGE_DEFAULT');
    }

    public function getStatusTextAttribute()
    {
        return Common::PRODUCT_STATUS[$this->status] ?? null;
    }

    public function getTypeTextAttribute()
    {
        return Common::PRODUCT_TYPE[$this->type] ?? null;
    }

    public function attributes()
    {
        return $this->hasMany(StockProduct::class)
            ->where('product_type', 'variable');
    }

    public function galleryImages()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function combo()
    {
        return $this->hasMany(StockProduct::class, 'product_id')
            ->where('product_type', 'combo');
    }
}
