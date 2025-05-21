<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Display truongssg of t    esource
     */
    public function recommend(Request $request)
    {
        try {
            $customerId = $request->input('customer_id');

            $sub = DB::table('order_items')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.customer_id', $customerId)
                ->where('orders.status', 'completed')
                ->select(
                    'order_items.id as item_id',
                    'order_items.product_id',
                    'order_items.stock_id',
                    'order_items.attribute_first_id',
                    'order_items.attribute_second_id',
                    'order_items.sell_price',
                    'order_items.quantity',
                    'orders.transaction_date',
                    DB::raw("ROW_NUMBER() OVER (
            PARTITION BY order_items.product_id, order_items.attribute_first_id, order_items.attribute_second_id
            ORDER BY orders.transaction_date DESC
        ) as rn")
                );

            $recommend = DB::table(DB::raw("({$sub->toSql()}) as recent_items"))
                ->mergeBindings($sub)
                ->where('recent_items.rn', 1)
                ->join('products', 'products.id', '=', 'recent_items.product_id')
                ->leftJoin('attributes as a1', 'a1.id', '=', 'recent_items.attribute_first_id')
                ->leftJoin('attributes as a2', 'a2.id', '=', 'recent_items.attribute_second_id')
                ->leftJoin('stocks', 'stocks.id', '=', 'recent_items.stock_id')
                ->select(
                    'products.id as product_id',
                    'products.name as product_name',
                    'products.type as product_type',
                    'stocks.name as stock_name',
                    'a1.title as attribute_1',
                    'a2.title as attribute_2',
                    'recent_items.sell_price',
                    'recent_items.quantity',
                    'recent_items.transaction_date'
                )
                ->orderByDesc('recent_items.transaction_date')
                ->get();
            return response()->json($recommend);
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
