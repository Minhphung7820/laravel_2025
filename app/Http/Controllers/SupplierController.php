<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        try {
            $suppliers = Customer::where('is_customer', 0)
                ->when($request->filled('keyword'), function ($query) use ($request) {
                    $query->where(function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request['keyword'] . '%')
                            ->orWhere('email', 'like', '%' . $request['keyword'] . '%');
                    });
                })
                ->orderByDesc('created_at')
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

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => [
                'required',
                'string',
                'max:20',
                \Illuminate\Validation\Rule::unique('customers', 'phone')
                    ->where(fn($q) => $q->where('is_customer', 0)),
            ],
            'email'            => 'nullable|email|max:255',
            'birthday'         => 'nullable|date',
            'gender'           => 'nullable|in:male,female,other,unknown',
            'address'          => 'nullable|string|max:255',
            'type'             => 'in:individual,company',
            'status'           => 'in:active,inactive,blacklist',
            'assigned_user_id' => 'nullable|exists:users,id',
            'facebook_url'     => 'nullable|string|max:255',
            'zalo_phone'       => 'nullable|string|max:20',
            'tax_code'         => 'nullable|string|max:100',
            'debt_amount'      => 'nullable|numeric',
            'credit_limit'     => 'nullable|numeric',
            'note'             => 'nullable|string',
            'avatar'           => 'nullable|image|max:2048',
        ]);

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

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => [
                'required',
                'string',
                'max:20',
                \Illuminate\Validation\Rule::unique('customers')
                    ->where(fn($q) => $q->where('is_customer', 0))
                    ->ignore($supplier->id),
            ],
            'email'            => 'nullable|email|max:255',
            'birthday'         => 'nullable|date',
            'gender'           => 'nullable|in:male,female,other,unknown',
            'address'          => 'nullable|string|max:255',
            'type'             => 'required|in:individual,company',
            'source'           => 'nullable|string|max:255',
            'status'           => 'required|in:active,inactive,blacklist',
            'assigned_user_id' => 'nullable|exists:users,id',
            'facebook_url'     => 'nullable|string|max:255',
            'zalo_phone'       => 'nullable|string|max:20',
            'tax_code'         => 'nullable|string|max:50',
            'debt_amount'      => 'nullable|numeric|min:0',
            'credit_limit'     => 'nullable|numeric|min:0',
            'note'             => 'nullable|string',
            'avatar'           => 'nullable|image|max:2048',
        ]);

        $validated['is_customer'] = 0;

        if ($request->hasFile('avatar')) {
            if ($supplier->avatar && Storage::disk('public')->exists(str_replace('/storage/', '', $supplier->avatar))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $supplier->avatar));
            }
            $validated['avatar'] = $this->uploadFile($request->file('avatar'), 'avatars');
        }

        $supplier->update($validated);

        return $this->responseSuccess($supplier, 'Cập nhật nhà cung cấp thành công');
    }

    public function destroy(string $id)
    {
        return $this->responseError('Tính năng đang phát triển', 501);
    }
}
