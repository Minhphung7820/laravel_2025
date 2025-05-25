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
        'have_serial',
        'image_cover',
        'unit_id',
        'category_id',
        'brand_id',
        'supplier_id',
        'warranty'
    ];

    protected $appends = [
        'status_text',
        'type_text'
    ];

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
}
