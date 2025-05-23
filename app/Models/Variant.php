<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    protected $fillable = [
        'category_id',
        'title',
    ];

    public function categories()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function attributes()
    {
        return $this->hasMany(Attribute::class);
    }
}
