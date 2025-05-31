<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function topCustomers(Request $request)
    {
        try {
            $topCustomers = DB::table('orders')
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->join('customers', 'orders.customer_id', '=', 'customers.id')
                ->where('orders.status', 'completed')
                ->where('orders.transaction_date', '>=', now()->subDays(30))
                ->groupBy('orders.customer_id', 'customers.name')
                ->havingRaw('COUNT(DISTINCT orders.id) >= 2')
                ->havingRaw('SUM(order_items.sell_price * order_items.quantity) > 10000000')
                ->select(
                    'orders.customer_id',
                    'customers.name',
                    DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
                    DB::raw('SUM(order_items.sell_price * order_items.quantity) as total_spent')
                )
                ->orderByDesc('total_spent')
                ->limit(10)
                ->get();

            return response()->json($topCustomers);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function topProducts(Request $request)
    {
        try {
            $topProducts = DB::table('orders')
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->leftJoin('attributes as a1', 'order_items.attribute_first_id', '=', 'a1.id')
                ->leftJoin('attributes as a2', 'order_items.attribute_second_id', '=', 'a2.id')
                ->where('orders.status', 'completed')
                ->whereBetween('orders.transaction_date', [
                    now()->subDays(30),
                    now()
                ])
                ->groupBy(
                    'order_items.product_id',
                    'order_items.attribute_first_id',
                    'order_items.attribute_second_id',
                    'products.name',
                    'a1.title',
                    'a2.title'
                )
                ->select(
                    'products.id as product_id',
                    'products.name as product_name',
                    'a1.title as attribute_1',
                    'a2.title as attribute_2',
                    DB::raw('SUM(order_items.quantity) as quantity_sold')
                )
                ->orderByDesc('quantity_sold')
                ->limit(10)
                ->get();

            return response()->json($topProducts);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
