<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $customers = Customer::when(
                isset($request['keyword']) && $request['keyword'],
                function ($query) use ($request) {
                    return $query->where(function ($query) use ($request) {
                        $query->where('name', 'like', '%' . $request['keyword'] . '%');
                        $query->orWhere('email', 'like', '%' . $request['keyword'] . '%');
                    });
                }
            )->orderBy('created_at', 'desc')->paginate($request['limit'] ?? 10);
            return response()->json($customers);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($request->assigned_user_id === 'null') {
            $request->merge(['assigned_user_id' => null]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone',
            'email' => 'nullable|email|max:255',
            'birthday' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other,unknown',
            'address' => 'nullable|string|max:255',
            'type' => 'in:individual,company',
            'status' => 'in:active,inactive,blacklist',
            'assigned_user_id' => 'nullable|exists:users,id',
            'facebook_url' => 'nullable|string|max:255',
            'zalo_phone' => 'nullable|string|max:20',
            'tax_code' => 'nullable|string|max:100',
            'debt_amount' => 'nullable|numeric',
            'credit_limit' => 'nullable|numeric',
            'note' => 'nullable|string',
            'avatar' => 'nullable|image|max:2048'
        ]);

        $validated['total_orders'] = 0;
        $validated['total_spent'] = 0;

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = '/storage/' . $path;
        }
        $customer = Customer::create($validated);

        return response()->json([
            'message' => 'Tạo khách hàng thành công',
            'data' => $customer
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = Customer::findOrFail($id);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $customer = Customer::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => [
                'required',
                'string',
                'max:20',
                \Illuminate\Validation\Rule::unique('customers')
                    ->ignore($customer->id),
            ],
            'email' => 'nullable|email',
            'birthday' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other,unknown',
            'address' => 'nullable|string|max:255',
            'type' => 'required|in:individual,company',
            'source' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive,blacklist',
            'assigned_user_id' => 'nullable|exists:users,id',
            'facebook_url' => 'nullable|string|max:255',
            'zalo_phone' => 'nullable|string|max:20',
            'tax_code' => 'nullable|string|max:50',
            'debt_amount' => 'nullable|numeric|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'note' => 'nullable|string',
            'avatar' => 'nullable|image|max:2048', // 2MB
        ]);

        // Nếu có file ảnh mới được gửi lên
        if ($request->hasFile('avatar')) {
            // Xóa ảnh cũ nếu có
            if ($customer->avatar && Storage::exists($customer->avatar)) {
                Storage::delete($customer->avatar);
            }

            // Lưu ảnh mới
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = '/storage/' . $avatarPath;
        }

        $customer->update($validated);

        return response()->json([
            'message' => 'Cập nhật thành công!',
            'customer' => $customer
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
