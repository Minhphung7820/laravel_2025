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
        'ward_id',
        'code',
        'tags',
        'customer_group_id',
        'assigned_user_id',
        'total_orders',
        'total_spent',
        'rating',
        'last_order_date',
        'last_contacted_at',
        'vip_at',
        'customer_stage',
        'contact_frequency_days',
        'preferred_contact',
        'type',
        'company_name',
        'position',
        'website_url',
        'number_of_employees',
        'revenue_estimate',
        'source',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'referral_code',
        'status',
        'facebook_url',
        'zalo_phone',
        'tax_code',
        'debt_amount',
        'credit_limit',
        'note',
        'marketing_consent',
        'is_customer',
        'avatar',
        'company_province_id',
        'company_district_id',
        'company_ward_id',
        'founded_at',
        'representative_name',
        'company_address',
        'company_tax_code',
        'country_code'
    ];

    protected $casts = [
        'birthday'          => 'date',
        'last_order_date'   => 'date',
        'vip_at'            => 'date',
        'tags'              => 'array',
        'debt_amount'       => 'decimal:2',
        'credit_limit'      => 'decimal:2',
        'total_spent'       => 'decimal:2',
        'marketing_consent' => 'boolean'
    ];

    protected $appends = ['avatar_url'];

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function getAvatarUrlAttribute()
    {
        return $this->avatar ? url($this->avatar) : env('AVATAR_DEFAULT');
    }
}
