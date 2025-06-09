<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        try {
            $suppliers = Customer::where('customers.is_customer', 0)
                ->join(
                    'provinces',
                    DB::raw("CONVERT(customers.company_province_id USING utf8mb4) COLLATE utf8mb4_unicode_ci"),
                    '=',
                    DB::raw("CONVERT(provinces.code USING utf8mb4) COLLATE utf8mb4_unicode_ci")
                )
                ->join(
                    'districts',
                    DB::raw("CONVERT(customers.company_district_id USING utf8mb4) COLLATE utf8mb4_unicode_ci"),
                    '=',
                    DB::raw("CONVERT(districts.code USING utf8mb4) COLLATE utf8mb4_unicode_ci")
                )
                ->join(
                    'wards',
                    DB::raw("CONVERT(customers.company_ward_id USING utf8mb4) COLLATE utf8mb4_unicode_ci"),
                    '=',
                    DB::raw("CONVERT(wards.code USING utf8mb4) COLLATE utf8mb4_unicode_ci")
                )
                ->when($request->filled('keyword'), function ($query) use ($request) {
                    $keyword = $request->input('keyword');
                    $query->where(function ($q) use ($keyword) {
                        $q->where('customers.name', 'like', "%{$keyword}%")
                            ->orWhere('customers.email', 'like', "%{$keyword}%")
                            ->orWhere('customers.phone', 'like', "%{$keyword}%")
                            ->orWhere('customers.code', 'like', "%{$keyword}%");
                    });
                })
                ->when($request->filled('code'), fn($q) => $q->where('customers.code', 'like', '%' . $request->input('code') . '%'))
                ->when($request->filled('name'), fn($q) => $q->where('customers.name', 'like', '%' . $request->input('name') . '%'))
                ->when($request->filled('email'), fn($q) => $q->where('customers.email', 'like', '%' . $request->input('email') . '%'))
                ->when($request->filled('phone'), fn($q) => $q->where('customers.phone', 'like', '%' . $request->input('phone') . '%'))
                ->when($request->filled('from_date'), fn($q) => $q->whereDate('customers.created_at', '>=', $request->input('from_date')))
                ->when($request->filled('to_date'), fn($q) => $q->whereDate('customers.created_at', '<=', $request->input('to_date')))
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

            return $this->responseSuccess($suppliers);
        } catch (\Exception $e) {
            return $this->responseError('Lỗi khi lấy danh sách nhà cung cấp', 500, $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        if ($request->assigned_user_id === 'null') {
            $request->merge(['assigned_user_id' => null]);
        }

        $rules = [
            'name'    => 'required|string|max:255',
            'code'    => [
                'required',
                'string',
                'max:100',
                \Illuminate\Validation\Rule::unique('customers', 'code')
                    ->where(fn($q) => $q->where('is_customer', 0)),
            ],
            'phone'   => [
                'required',
                'string',
                'max:20',
                \Illuminate\Validation\Rule::unique('customers', 'phone')
                    ->where(fn($q) => $q->where('is_customer', 0)),
            ],
            'email'            => 'nullable|email|max:255',
            'address'          => 'required|string|max:255',
            'status'           => 'in:active,inactive,blacklist',
            'tax_code'         => 'nullable|string|max:100',
            'debt_amount'      => 'nullable|numeric',
            'credit_limit'     => 'nullable|numeric',
            'note'             => 'nullable|string',
            'avatar'           => 'nullable|image|max:2048',
            'country_code'     => 'required|string|max:5',
            'company_province_id'      => 'nullable|string|max:50',
            'company_district_id'      => 'nullable|string|max:50',
            'company_ward_id'          => 'nullable|string|max:50',
        ];

        if ($request->input('country_code') === 'VN') {
            $rules['company_province_id'] = 'required|string|max:50';
            $rules['company_district_id'] = 'required|string|max:50';
            $rules['company_ward_id']     = 'required|string|max:50';
        }

        $validated = $request->validate($rules);

        $validated['total_orders'] = 0;
        $validated['total_spent'] = 0;
        $validated['is_customer'] = 0;

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $this->uploadFile($request->file('avatar'), 'avatars');
        }

        $supplier = Customer::create($validated);

        return $this->responseSuccess($supplier, 'Tạo nhà cung cấp thành công', 201);
    }

    public function show(string $id)
    {
        try {
            $supplier = Customer::findOrFail($id);
            return $this->responseSuccess($supplier);
        } catch (\Exception $e) {
            return $this->responseError('Không tìm thấy nhà cung cấp', 404, $e->getMessage());
        }
    }

    public function update(Request $request, string $id)
    {
        $supplier = Customer::findOrFail($id);

        $rules = [
            'name'       => 'required|string|max:255',
            'code'       => [
                'required',
                'string',
                'max:100',
                \Illuminate\Validation\Rule::unique('customers', 'code')
                    ->where(fn($q) => $q->where('is_customer', 0))
                    ->ignore($supplier->id),
            ],
            'phone'      => [
                'required',
                'string',
                'max:20',
                \Illuminate\Validation\Rule::unique('customers', 'phone')
                    ->where(fn($q) => $q->where('is_customer', 0))
                    ->ignore($supplier->id),
            ],
            'email'         => 'nullable|email|max:255',
            'address'       => 'nullable|string|max:255',
            'company_province_id'   => 'nullable|string|max:20',
            'company_district_id'   => 'nullable|string|max:20',
            'company_ward_id'       => 'nullable|string|max:20',
            'country_code'  => 'nullable|string|max:10',
            'status'        => 'required|in:active,inactive,blacklist',
            'tax_code'      => 'nullable|string|max:50',
            'debt_amount'   => 'nullable|numeric|min:0',
            'credit_limit'  => 'nullable|numeric|min:0',
            'note'          => 'nullable|string|max:1000',
            'avatar'        => 'nullable|image|max:2048',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($request->country_code === 'VN') {
            $validator->addRules([
                'company_province_id' => 'required|string|max:20',
                'company_district_id' => 'required|string|max:20',
                'company_ward_id'     => 'required|string|max:20',
            ]);
        }

        $validated = $validator->validate();

        $validated['is_customer'] = 0;
        $validated['type'] = 'company';

        if ($request->hasFile('avatar')) {
            if ($supplier->avatar && Storage::disk('public')->exists(str_replace('/storage/', '', $supplier->avatar))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $supplier->avatar));
            }

            $validated['avatar'] = $this->uploadFile($request->file('avatar'), 'avatars');
        }

        if ($request->has('remove_avatar') && $request->boolean('remove_avatar')) {
            if ($supplier->avatar && Storage::disk('public')->exists(str_replace('/storage/', '', $supplier->avatar))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $supplier->avatar));
            }
            $validated['avatar'] = null;
        }

        $supplier->update($validated);

        return $this->responseSuccess($supplier, 'Cập nhật nhà cung cấp thành công');
    }

    public function destroy(string $id)
    {
        return $this->responseError('Tính năng đang phát triển', 501);
    }
}
