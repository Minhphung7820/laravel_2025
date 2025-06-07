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
                throw new \Exception(__('common.category.delete_has_variant'));
            }

            // Nếu có Product liên quan
            if (\App\Models\Product::where('category_id', $category->id)->exists()) {
                throw new \Exception(__('common.category.delete_in_use'));
            }
        });
    }

    public function variants()
    {
        return $this->hasMany(Variant::class, 'category_id');
    }
}
