<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    protected $fillable = [
        'title',
        'variant_id',
    ];

    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }
}
