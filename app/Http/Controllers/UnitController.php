<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        try {
            $units = Unit::when($request->filled('keyword'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request['keyword'] . '%')
                        ->orWhere('abbreviation', 'like', '%' . $request['keyword'] . '%');
                });
            })
                ->orderByDesc('created_at')
                ->paginate($request->input('limit', 10));

            return $this->responseSuccess($units);
        } catch (\Exception $e) {
            return $this->responseError('Lỗi khi lấy danh sách đơn vị', 500, $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'abbreviation' => 'nullable|string|max:50',
        ]);

        $unit = Unit::create($validated);

        return $this->responseSuccess($unit, 'Tạo đơn vị thành công', 201);
    }

    public function show($id)
    {
        try {
            $unit = Unit::findOrFail($id);
            return $this->responseSuccess($unit);
        } catch (\Exception $e) {
            return $this->responseError('Không tìm thấy đơn vị', 404, $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $unit = Unit::findOrFail($id);

            $validated = $request->validate([
                'name'         => 'required|string|max:255',
                'abbreviation' => 'nullable|string|max:50',
            ]);

            $unit->update($validated);

            return $this->responseSuccess($unit, 'Cập nhật đơn vị thành công');
        } catch (\Exception $e) {
            return $this->responseError('Lỗi khi cập nhật đơn vị', 500, $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        return $this->responseError('Tính năng đang phát triển', 501);
    }
}
