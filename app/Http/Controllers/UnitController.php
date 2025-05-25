<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $customers = Unit::when(
                isset($request['keyword']) && $request['keyword'],
                function ($query) use ($request) {
                    return $query->where(function ($query) use ($request) {
                        $query->where('name', 'like', '%' . $request['keyword'] . '%');
                        $query->orWhere('abbreviation', 'like', '%' . $request['keyword'] . '%');
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
            'name'         => 'required|string|max:255',
            'abbreviation' => 'nullable|string|max:50'
        ]);

        $unit = Unit::create($validated);

        return response()->json(['message' => 'Đã tạo đơn vị', 'data' => $unit]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $unit = Unit::findOrFail($id);
        return response()->json($unit);
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
        $unit = Unit::findOrFail($id);

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'abbreviation' => 'nullable|string|max:50'
        ]);

        $unit->update($validated);

        return response()->json(['message' => 'Đã cập nhật đơn vị', 'data' => $unit]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
