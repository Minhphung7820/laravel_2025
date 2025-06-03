<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    /**
     * Hiển thị danh sách thương hiệu có tìm kiếm.
     */
    public function index(Request $request)
    {
        try {
            $brands = Brand::when(
                $request->filled('keyword'),
                function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->keyword . '%');
                }
            )->orderByDesc('created_at')
                ->paginate($request->input('limit', 10));

            return $this->responseSuccess($brands);
        } catch (\Exception $e) {
            return $this->responseError('Lỗi truy vấn', 500, $e->getMessage());
        }
    }

    /**
     * Tạo mới thương hiệu.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $this->uploadFile($request->file('logo'), 'brands');
        }

        $brand = Brand::create($validated);

        return $this->responseSuccess($brand, 'Đã tạo thương hiệu');
    }

    /**
     * Xem chi tiết thương hiệu.
     */
    public function show($id)
    {
        $brand = Brand::findOrFail($id);
        return $this->responseSuccess($brand);
    }

    /**
     * Cập nhật thương hiệu.
     */
    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_logo' => 'nullable|boolean',
        ]);

        // Xóa logo cũ nếu được yêu cầu
        if ($request->boolean('remove_logo')) {
            if ($brand->logo && Storage::disk('public')->exists(str_replace('/storage/', '', $brand->logo))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $brand->logo));
            }
            $validated['logo'] = null;
        }

        // Cập nhật logo mới
        if ($request->hasFile('logo')) {
            if ($brand->logo && Storage::disk('public')->exists(str_replace('/storage/', '', $brand->logo))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $brand->logo));
            }
            $validated['logo'] = $this->uploadFile($request->file('logo'), 'brands');
        }

        $brand->update($validated);

        return $this->responseSuccess($brand, 'Đã cập nhật thương hiệu');
    }

    /**
     * Xóa thương hiệu (chưa làm).
     */
    public function destroy(string $id)
    {
        return $this->responseError('Chức năng đang phát triển', 501);
    }
}
