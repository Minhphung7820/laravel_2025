<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'birthday',
        'gender',
        'address',
        'province_id',
        'district_id',
        'code',
        'tags',
        'assigned_user_id',
        'total_orders',
        'total_spent',
        'last_order_date',
        'vip_at',
        'type',
        'source',
        'status',
        'facebook_url',
        'zalo_phone',
        'tax_code',
        'debt_amount',
        'credit_limit',
        'note',
        'is_customer',
        'avatar'
    ];

    protected $casts = [
        'birthday' => 'date',
        'last_order_date' => 'date',
        'vip_at' => 'date',
        'tags' => 'array',
        'debt_amount' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'total_spent' => 'decimal:2',
    ];

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }
}
