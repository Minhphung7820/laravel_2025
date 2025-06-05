<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    protected $fillable = [
        'title',
        'variant_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($attribute) {
            $used = \App\Models\StockProduct::where(function ($q) use ($attribute) {
                $q->where('attribute_first_id', $attribute->id)
                    ->orWhere('attribute_second_id', $attribute->id);
            })->exists();

            if ($used) {
                throw new \Exception('Không thể xóa thuộc tính vì đang được sử dụng trong kho.');
            }
        });
    }

    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }
}
