<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariantImage;
use App\Models\StockProduct;
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
        DB::beginTransaction();

        try {
            // Ảnh bìa
            $coverPath = null;
            if ($request->hasFile('cover_image')) {
                $coverPath = $request->file('cover_image')->store('products/cover', 'public');
            }

            // Tạo sản phẩm chính
            $product = Product::create([
                'name'           => $request->input('name'),
                'sku'            => $request->input('sku'),
                'barcode'        => $request->input('barcode'),
                'type'           => $request->input('type'),
                'have_variant'   => $request->boolean('has_variant'),
                'have_serial'    => $request->boolean('has_serial'),
                'warranty'       => $request->input('warranty'),
                'unit_id'        => $request->input('unit_id'),
                'brand_id'       => $request->input('brand_id'),
                'category_id'    => $request->input('category_id'),
                'supplier_id'    => $request->input('supplier_id'),
                'image_cover'    => $coverPath ? '/storage/' . $coverPath : null,
                'status'         => 'pending',
            ]);

            // Lưu ảnh chính (gallery_images) hàng loạt
            if ($request->hasFile('gallery_images')) {
                $imagesData = [];
                foreach ($request->file('gallery_images') as $file) {
                    $path = $file->store('products/gallery', 'public');
                    $imagesData[] = [
                        'product_id' => $product->id,
                        'image'      => '/storage/' . $path,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                ProductImage::insert($imagesData);
            }

            // Lưu stock_data hàng loạt
            $stockData = json_decode($request->input('stock_data'), true);
            if (is_array($stockData)) {
                $stockInsert = [];
                foreach ($stockData as $stockId => $item) {
                    $stockInsert[] = [
                        'stock_id'             => $stockId,
                        'product_id'           => $product->id,
                        'quantity'             => $item['qty'] ?? 0,
                        'purchase_price'       => $item['cost_price'] ?? 0,
                        'sell_price'           => $item['sale_price'] ?? 0,
                        'max_discount_percent' => $item['max_discount_percent'] ?? 0,
                        'max_increase_percent' => $item['max_increase_percent'] ?? 0,
                        'auto_calc'            => $item['auto_calc'] ?? false,
                        'is_product_variant'   => 0,
                        'created_at'           => now(),
                        'updated_at'           => now(),
                    ];
                }
                StockProduct::insert($stockInsert);
            }

            // Lưu biến thể (dùng create do cần lấy ID)
            if ($request->has('variants')) {
                foreach ($request->input('variants') as $i => $variant) {
                    $variantStock = StockProduct::create([
                        'stock_id'             => $variant['stock_id'],
                        'product_id'           => $product->id,
                        'attribute_first_id'   => $variant['attributes'][0]['value_id'] ?? null,
                        'attribute_second_id'  => $variant['attributes'][1]['value_id'] ?? null,
                        'quantity'             => $variant['quantity'],
                        'sell_price'           => $variant['sell_price'],
                        'purchase_price'       => $variant['purchase_price'],
                        'sku'                  => $variant['sku'] ?: $this->generateUniqueSku($request->input('sku') ?? 'SP'),
                        'barcode'              => $variant['barcode'],
                        'is_sale'              => $variant['is_sale'],
                        'is_product_variant'   => 1,
                        'product_type'         => 'variable'
                    ]);

                    // Lưu ảnh cho biến thể
                    if ($request->hasFile("variants.$i.image")) {
                        $file = $request->file("variants.$i.image");
                        $path = $file->store('products/variants', 'public');

                        ProductVariantImage::create([
                            'stock_product_id' => $variantStock->id,
                            'image'            => '/storage/' . $path,
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json(['message' => 'Thêm sản phẩm thành công!'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    protected function generateUniqueSku($prefix)
    {
        do {
            $suffix = strtoupper(\Illuminate\Support\Str::random(6));
            $sku = "{$prefix}-{$suffix}";
        } while (StockProduct::where('sku', $sku)->exists());

        return $sku;
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
