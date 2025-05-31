<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $customers = Stock::when(
                isset($request['keyword']) && $request['keyword'],
                function ($query) use ($request) {
                    return $query->where(function ($query) use ($request) {
                        $query->where('name', 'like', '%' . $request['keyword'] . '%');
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
        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $stock = Stock::create($validated);

        return response()->json(['message' => 'Đã tạo kho', 'data' => $stock]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $stock = Stock::findOrFail($id);
        return response()->json($stock);
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
    public function update(Request $request, $id)
    {
        $stock = Stock::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $stock->update($validated);

        return response()->json(['message' => 'Đã cập nhật kho', 'data' => $stock]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
