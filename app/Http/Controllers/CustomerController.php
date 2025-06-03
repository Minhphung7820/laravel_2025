<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        try {
            $customers = Customer::where('is_customer', 1)
                ->when($request->filled('keyword'), function ($query) use ($request) {
                    $query->where(function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request['keyword'] . '%')
                            ->orWhere('email', 'like', '%' . $request['keyword'] . '%');
                    });
                })
                ->orderByDesc('created_at')
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

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => [
                'required',
                'string',
                'max:20',
                \Illuminate\Validation\Rule::unique('customers', 'phone')
                    ->where(fn($q) => $q->where('is_customer', 1)),
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
            'avatar'           => 'nullable|image|max:2048'
        ]);

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

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => [
                'required',
                'string',
                'max:20',
                \Illuminate\Validation\Rule::unique('customers', 'phone')
                    ->where(fn($q) => $q->where('is_customer', 1))
                    ->ignore($customer->id),
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

        if ($request->hasFile('avatar')) {
            if ($customer->avatar && Storage::disk('public')->exists(str_replace('/storage/', '', $customer->avatar))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $customer->avatar));
            }

            $validated['avatar'] = $this->uploadFile($request->file('avatar'), 'avatars');
        }

        $customer->update($validated);

        return $this->responseSuccess($customer, 'Cập nhật khách hàng thành công');
    }

    public function destroy(string $id)
    {
        return $this->responseError('Tính năng đang phát triển', 501);
    }
}
