<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    protected $fillable = [
        'category_id',
        'title',
        'product_id'
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($variant) {
            // Nếu có attribute con
            if ($variant->attributes()->exists()) {
                throw new \Exception(__('common.variant.delete_has_attribute'));
            }

            // Nếu thuộc tính của variant đang được dùng trong stock_products
            $attributeIds = \App\Models\Attribute::where('variant_id', $variant->id)->pluck('id');

            $usedInStock = \App\Models\StockProduct::whereIn('attribute_first_id', $attributeIds)
                ->orWhereIn('attribute_second_id', $attributeIds)
                ->exists();

            if ($usedInStock) {
                throw new \Exception(__('common.variant.delete_used_in_stock'));
            }
        });
    }

    public function categories()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function attributes()
    {
        return $this->hasMany(Attribute::class);
    }

    public function values()
    {
        return $this->hasMany(Attribute::class, 'variant_id');
    }
}
