<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'title',
    ];

    public function variants()
    {
        return $this->hasMany(Variant::class, 'category_id');
    }
}
