<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = ['name'];

    public function stockProducts()
    {
        return $this->hasMany(StockProduct::class);
    }
}
