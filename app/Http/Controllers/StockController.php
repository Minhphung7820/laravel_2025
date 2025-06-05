<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        try {
            $stocks = Stock::when($request->filled('keyword'), function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request['keyword'] . '%');
            })->when(isset($request['except_ids']) && $request['except_ids'], function ($query) use ($request) {
                return $query->whereNotIn('id', explode(",", $request['except_ids']));
            })->when(isset($request['is_default']), function ($query) use ($request) {
                return $query->where('is_default', $request['is_default']);
            })
                ->orderByDesc('created_at')
                ->paginate($request->input('limit', 10));

            return $this->responseSuccess($stocks);
        } catch (\Exception $e) {
            return $this->responseError('Lỗi khi lấy danh sách kho', 500, $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $stock = Stock::create($validated);

        return $this->responseSuccess($stock, 'Tạo kho thành công', 201);
    }

    public function show($id)
    {
        try {
            $stock = Stock::findOrFail($id);
            return $this->responseSuccess($stock);
        } catch (\Exception $e) {
            return $this->responseError('Không tìm thấy kho', 404, $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $stock = Stock::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255'
            ]);

            $stock->update($validated);

            return $this->responseSuccess($stock, 'Cập nhật kho thành công');
        } catch (\Exception $e) {
            return $this->responseError('Lỗi khi cập nhật kho', 500, $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        return $this->responseError('Tính năng đang phát triển', 501);
    }
}
