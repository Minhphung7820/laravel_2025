<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = [
        'name',
        'description',
        'logo',
    ];

    // Ví dụ nếu m muốn có đường dẫn đầy đủ logo:
    protected $appends = ['logo_url'];

    public function getLogoUrlAttribute()
    {
        return $this->logo ? url($this->logo) : null;
    }
}
