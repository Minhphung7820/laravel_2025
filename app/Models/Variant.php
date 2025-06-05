<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    protected $fillable = [
        'category_id',
        'title',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($variant) {
            // Nếu có attribute con
            if ($variant->attributes()->exists()) {
                throw new \Exception('Không thể xóa biến thể vì có thuộc tính liên kết.');
            }

            // Nếu thuộc tính của variant đang được dùng trong stock_products
            $attributeIds = \App\Models\Attribute::where('variant_id', $variant->id)->pluck('id');

            $usedInStock = \App\Models\StockProduct::whereIn('attribute_first_id', $attributeIds)
                ->orWhereIn('attribute_second_id', $attributeIds)
                ->exists();

            if ($usedInStock) {
                throw new \Exception('Không thể xóa biến thể vì thuộc tính đang được sử dụng trong tồn kho.');
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
}
