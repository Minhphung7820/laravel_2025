<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'title',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($category) {
            // Nếu có Variant con
            if ($category->variants()->exists()) {
                throw new \Exception('Không thể xóa danh mục vì có biến thể liên kết.');
            }

            // Nếu có Product liên quan
            if (\App\Models\Product::where('category_id', $category->id)->exists()) {
                throw new \Exception('Không thể xóa danh mục vì đang được sử dụng bởi sản phẩm.');
            }
        });
    }

    public function variants()
    {
        return $this->hasMany(Variant::class, 'category_id');
    }
}
