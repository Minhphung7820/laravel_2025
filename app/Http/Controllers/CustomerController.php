<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        try {
            $customers = Customer::query()
                ->join(
                    'provinces',
                    DB::raw("CONVERT(customers.province_id USING utf8mb4) COLLATE utf8mb4_unicode_ci"),
                    '=',
                    DB::raw("CONVERT(provinces.code USING utf8mb4) COLLATE utf8mb4_unicode_ci")
                )
                ->join(
                    'districts',
                    DB::raw("CONVERT(customers.district_id USING utf8mb4) COLLATE utf8mb4_unicode_ci"),
                    '=',
                    DB::raw("CONVERT(districts.code USING utf8mb4) COLLATE utf8mb4_unicode_ci")
                )
                ->join(
                    'wards',
                    DB::raw("CONVERT(customers.ward_id USING utf8mb4) COLLATE utf8mb4_unicode_ci"),
                    '=',
                    DB::raw("CONVERT(wards.code USING utf8mb4) COLLATE utf8mb4_unicode_ci")
                )
                ->where('customers.is_customer', 1)
                ->when($request->filled('keyword'), function ($query) use ($request) {
                    $query->where(function ($q) use ($request) {
                        $q->where('customers.name', 'like', '%' . $request->keyword . '%')
                            ->orWhere('customers.email', 'like', '%' . $request->keyword . '%');
                    });
                })
                ->when($request->filled('code'), fn ($q) => $q->where('customers.code', 'like', '%' . $request->code . '%'))
                ->when($request->filled('name'), fn ($q) => $q->where('customers.name', 'like', '%' . $request->name . '%'))
                ->when($request->filled('email'), fn ($q) => $q->where('customers.email', 'like', '%' . $request->email . '%'))
                ->when($request->filled('phone'), fn ($q) => $q->where('customers.phone', 'like', '%' . $request->phone . '%'))
                ->when($request->filled('type'), fn ($q) => $q->where('customers.type', $request->type))
                ->when($request->filled('from_date'), fn ($q) => $q->whereDate('customers.created_at', '>=', $request->from_date))
                ->when($request->filled('to_date'), fn ($q) => $q->whereDate('customers.created_at', '<=', $request->to_date))
                ->select([
                    'customers.id',
                    'customers.name',
                    'customers.avatar',
                    'customers.email',
                    'customers.phone',
                    'customers.address',
                    'customers.code',
                    'customers.type',
                    DB::raw("CONCAT(customers.address, ', ', wards.full_name, ', ', districts.full_name, ', ', provinces.full_name) as address_full")
                ])
                ->orderByDesc('customers.created_at')
                ->paginate($request->input('limit', 10));

            return $this->responseSuccess($customers);
        } catch (\Exception $e) {
            return $this->responseError('Lỗi khi lấy danh sách khách hàng', 500, $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        if ($request->assigned_user_id === 'null') {
            $request->merge(['assigned_user_id' => null]);
        }

        $rules = [
            // Thông tin cơ bản
            'name'  => 'required|string|max:255',
            'phone' => [
                'required',
                'string',
                'max:20',
                \Illuminate\Validation\Rule::unique('customers', 'phone')
                    ->where(fn ($q) => $q->where('is_customer', 1)),
            ],
            'code' => [
                'required',
                'string',
                'max:100',
                \Illuminate\Validation\Rule::unique('customers', 'code')
                    ->where(fn ($q) => $q->where('is_customer', 1)),
            ],
            'email'       => 'nullable|email|max:255',
            'birthday'    => 'nullable|date',
            'gender'      => 'nullable|in:male,female,other,unknown',
            'address'     => 'required|string|max:255',
            'province_id' => 'required|string|max:10',
            'district_id' => 'required|string|max:10',
            'ward_id'     => 'required|string|max:10',

            // Liên hệ
            'facebook_url'      => 'nullable|string|max:255',
            'zalo_phone'        => 'nullable|string|max:20',
            'source'            => 'nullable|string|max:255',
            'preferred_contact' => 'nullable|in:phone,email,zalo,sms',
            'last_contacted_at' => 'nullable|date',
            'rating'            => 'nullable|integer|min:1|max:5',

            // UTM
            'utm_source'   => 'nullable|string|max:255',
            'utm_medium'   => 'nullable|string|max:255',
            'utm_campaign' => 'nullable|string|max:255',

            // Phân loại
            'type'                   => 'required|in:individual,company',
            'status'                 => 'required|in:active,inactive,blacklist',
            'assigned_user_id'       => 'nullable|exists:users,id',
            'customer_group_id'      => 'nullable|exists:customer_groups,id',
            'customer_stage'         => 'nullable|in:new,prospect,converted,inactive,lost',
            'contact_frequency_days' => 'nullable|integer|min:1',

            // Công ty
            'position'            => 'nullable|string|max:100',
            'website_url'         => 'nullable|url|max:255',
            'number_of_employees' => 'nullable|integer|min:0',
            'revenue_estimate'    => 'nullable|numeric|min:0',
            'company_tax_code'    => 'nullable|string|max:100',
            'founded_at'          => 'nullable|date',

            // Công nợ
            'tax_code'          => 'nullable|string|max:100',
            'debt_amount'       => 'nullable|numeric|min:0',
            'credit_limit'      => 'nullable|numeric|min:0',
            'marketing_consent' => 'nullable',
            'referral_code'     => 'nullable|string|max:100',
            'note'              => 'nullable|string|max:5000',

            // Avatar
            'avatar' => 'nullable|image|max:2048',
        ];
        if ($request->type === 'company') {
            $rules = array_merge($rules, [
                'company_name'        => 'required|string|max:255',
                'company_address'     => 'required|string|max:255',
                'company_province_id' => 'required|string|max:10',
                'company_district_id' => 'required|string|max:10',
                'company_ward_id'     => 'required|string|max:10',
                'representative_name' => 'required|string|max:255',
            ]);
        }

        $validated = Validator::make($request->all(), $rules)->validate();

        $validated['marketing_consent'] = $validated['marketing_consent'] === 'true' ? 1 : 0;
        $validated['total_orders'] = 0;
        $validated['total_spent'] = 0;

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $this->uploadFile($request->file('avatar'), 'avatars');
        }

        $customer = Customer::create($validated);

        return $this->responseSuccess($customer, 'Tạo khách hàng thành công', 201);
    }

    public function show(string $id)
    {
        try {
            $customer = Customer::findOrFail($id);
            return $this->responseSuccess($customer);
        } catch (\Exception $e) {
            return $this->responseError('Không tìm thấy khách hàng', 404, $e->getMessage());
        }
    }

    public function update(Request $request, string $id)
    {
        $customer = Customer::findOrFail($id);

        if ($request->assigned_user_id === 'null') {
            $request->merge(['assigned_user_id' => null]);
        }

        $rules = [
            // Thông tin cơ bản
            'name'  => 'required|string|max:255',
            'phone' => [
                'required',
                'string',
                'max:20',
                \Illuminate\Validation\Rule::unique('customers', 'phone')
                    ->where(fn ($q) => $q->where('is_customer', 1))
                    ->ignore($customer->id),
            ],
            'code' => [
                'required',
                'string',
                'max:100',
                \Illuminate\Validation\Rule::unique('customers', 'code')
                    ->where(fn ($q) => $q->where('is_customer', 1))
                    ->ignore($customer->id),
            ],
            'email'       => 'nullable|email|max:255',
            'birthday'    => 'nullable|date',
            'gender'      => 'nullable|in:male,female,other,unknown',
            'address'     => 'required|string|max:255',
            'province_id' => 'required|string|max:10',
            'district_id' => 'required|string|max:10',
            'ward_id'     => 'required|string|max:10',

            // Liên hệ
            'facebook_url'      => 'nullable|string|max:255',
            'zalo_phone'        => 'nullable|string|max:20',
            'source'            => 'nullable|string|max:255',
            'preferred_contact' => 'nullable|in:phone,email,zalo,sms',
            'last_contacted_at' => 'nullable|date',
            'rating'            => 'nullable|integer|min:1|max:5',

            // UTM
            'utm_source'   => 'nullable|string|max:255',
            'utm_medium'   => 'nullable|string|max:255',
            'utm_campaign' => 'nullable|string|max:255',

            // Phân loại
            'type'                   => 'required|in:individual,company',
            'status'                 => 'required|in:active,inactive,blacklist',
            'assigned_user_id'       => 'nullable|exists:users,id',
            'customer_group_id'      => 'nullable|exists:customer_groups,id',
            'customer_stage'         => 'nullable|in:new,prospect,converted,inactive,lost',
            'contact_frequency_days' => 'nullable|integer|min:1',

            // Công ty
            'position'            => 'nullable|string|max:100',
            'website_url'         => 'nullable|url|max:255',
            'number_of_employees' => 'nullable|integer|min:0',
            'revenue_estimate'    => 'nullable|numeric|min:0',
            'company_tax_code'    => 'nullable|string|max:100',
            'founded_at'          => 'nullable|date',

            // Công nợ
            'tax_code'          => 'nullable|string|max:100',
            'debt_amount'       => 'nullable|numeric|min:0',
            'credit_limit'      => 'nullable|numeric|min:0',
            'marketing_consent' => 'nullable',
            'referral_code'     => 'nullable|string|max:100',
            'note'              => 'nullable|string|max:5000',

            // Avatar
            'avatar' => 'nullable|image|max:2048',
        ];

        if ($request->type === 'company') {
            $rules = array_merge($rules, [
                'company_name'        => 'required|string|max:255',
                'company_address'     => 'required|string|max:255',
                'company_province_id' => 'required|string|max:10',
                'company_district_id' => 'required|string|max:10',
                'company_ward_id'     => 'required|string|max:10',
                'representative_name' => 'required|string|max:255',
            ]);
        }

        $validated = Validator::make($request->all(), $rules)->validate();

        $validated['marketing_consent'] = $validated['marketing_consent'] === 'true' ? 1 : 0;

        if ($request->hasFile('avatar')) {
            if ($customer->avatar && Storage::disk('public')->exists(str_replace('/storage/', '', $customer->avatar))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $customer->avatar));
            }

            $validated['avatar'] = $this->uploadFile($request->file('avatar'), 'avatars');
        }

        if ($request->has('remove_avatar') && $request->boolean('remove_avatar')) {
            if ($customer->avatar && Storage::disk('public')->exists(str_replace('/storage/', '', $customer->avatar))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $customer->avatar));
            }
            $validated['avatar'] = null;
        }

        if ($validated['type'] === 'individual') {
            $validated['company_name'] = null;
            $validated['position'] = null;
            $validated['website_url'] = null;
            $validated['number_of_employees'] = null;
            $validated['revenue_estimate'] = null;
            $validated['company_address'] = null;
            $validated['company_province_id'] = null;
            $validated['company_district_id'] = null;
            $validated['company_ward_id'] = null;
            $validated['representative_name'] = null;
            $validated['company_tax_code'] = null;
            $validated['founded_at'] = null;
        }

        $customer->update($validated);

        return $this->responseSuccess($customer, 'Cập nhật khách hàng thành công');
    }

    public function destroy(string $id)
    {
        return $this->responseError('Tính năng đang phát triển', 501);
    }
}
