<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductAttributesResource;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariantImage;
use App\Models\StockProduct;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $limit = $request->input('limit', 20);
        $urlPrefix = url('/');

        $query = DB::table('stock_products as st')
            ->join('products as p', 'st.product_id', '=', 'p.id')
            ->join('stocks as s', function ($join) {
                $join->on('st.stock_id', '=', 's.id')
                    ->where('s.is_default', 1);
            })
            ->leftJoin('units as u', 'st.unit_id', '=', 'u.id')
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->where('p.type', 'variable')
                        ->where('st.product_type', 'variable');
                })->orWhere(function ($q2) {
                    $q2->whereIn('p.type', ['single', 'combo'])
                        ->where('st.product_type', 'root_stock');
                });
            });

        // Filter theo tên, sku
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('p.name', 'like', "%{$search}%")
                    ->orWhere('st.sku', 'like', "%{$search}%");
            });
        }

        // Filter theo status
        if ($status = $request->input('status')) {
            $query->where('p.status', $status);
        }

        // Chọn các cột cần thiết
        $query->select([
            'st.id',
            'st.product_id',
            'p.name as product_name',
            DB::raw("CASE WHEN p.type = 'variable' THEN st.sku ELSE p.sku END AS sku"),
            's.name as stock_name',
            'p.type as product_type',
            'st.purchase_price',
            'st.sell_price',
            'st.quantity',
            'u.name as unit_name',
            DB::raw("CASE
        WHEN p.type = 'variable' THEN (
            SELECT CONCAT('$urlPrefix', pvi.image)
            FROM product_variant_images pvi
            WHERE pvi.stock_product_id = st.id
            LIMIT 1
        )
        ELSE CONCAT('$urlPrefix', p.image_cover)
    END AS image"),
            'p.status',

            // ✅ Thêm field product_type_text
            DB::raw("CASE
        WHEN p.type = 'variable' THEN 'Sản phẩm biến thể'
        WHEN p.type = 'single' THEN 'Sản phẩm đơn'
        WHEN p.type = 'combo' THEN 'Sản phẩm combo'
        ELSE 'Không xác định'
    END AS product_type_text"),

            // ✅ Thêm field status_text
            DB::raw("CASE
        WHEN p.status = 'pending' THEN 'Đang chờ'
        WHEN p.status = 'approved' THEN 'Đã duyệt'
        ELSE 'Không rõ'
    END AS status_text"),
        ])
            ->orderByRaw("
            CASE
                WHEN p.type = 'variable' THEN st.created_at
                ELSE p.created_at
            END DESC
        ");

        $results = $query->paginate($limit);

        return response()->json($results);
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
                'name'         => $request->input('name'),
                'sku'          => $request->input('sku'),
                'barcode'      => $request->input('barcode'),
                'type'         => $request->input('type'),
                'have_variant' => $request->boolean('has_variant'),
                'have_serial'  => $request->boolean('has_serial'),
                'warranty'     => $request->input('warranty'),
                'unit_id'      => $request->input('unit_id'),
                'brand_id'     => $request->input('brand_id'),
                'category_id'  => $request->input('category_id'),
                'supplier_id'  => $request->input('supplier_id'),
                'image_cover'  => $coverPath ? '/storage/' . $coverPath : null,
                'status'       => 'pending',
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
                        'stock_id'            => $variant['stock_id'],
                        'product_id'          => $product->id,
                        'attribute_first_id'  => $variant['attributes'][0]['value_id'] ?? null,
                        'attribute_second_id' => $variant['attributes'][1]['value_id'] ?? null,
                        'quantity'            => $variant['quantity'],
                        'sell_price'          => $variant['sell_price'],
                        'purchase_price'      => $variant['purchase_price'],
                        'sku'                 => $variant['sku'] ?: $this->generateUniqueSku($request->input('sku') ?? 'SP'),
                        'barcode'             => $variant['barcode'],
                        'is_sale'             => $variant['is_sale'],
                        'is_product_variant'  => 1,
                        'product_type'        => 'variable'
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

    protected function generateUniqueSku($prefix, $id = null)
    {
        do {
            $suffix = strtoupper(\Illuminate\Support\Str::random(6));
            $sku = "{$prefix}-{$suffix}";
        } while (StockProduct::where('sku', $sku)->when($id, function ($q) use ($id) {
            return $q->where('id', '!=', $id);
        })->exists());

        return $sku;
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with([
            'attributes.attributeFirst.variant',
            'attributes.attributeSecond.variant',
            'attributes.variantImages',
            'galleryImages',
            'stockData'
        ])->findOrFail($id);

        return [
            'product'        => $product,
            'stock_products' => ProductAttributesResource::collection($product->attributes ?? []),
        ];
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
        DB::beginTransaction();
        try {
            $coverPath = null;
            if ($request->hasFile('cover_image')) {
                $coverPath = $request->file('cover_image')->store('products/cover', 'public');
            }
            $data = [
                'name'        => $request['name'],
                'sku'         => $request['sku'] ?? '',
                'barcode'     => $request['barcode'] ?? '',
                'has_serial'  => $request['has_serial'] ?? '',
                'warranty'    => $request['warranty'] ?? '',
                'unit_id'     => $request['unit_id'] ?? null,
                'brand_id'    => $request['brand_id'] ?? null,
                'category_id' => $request['category_id'] ?? null,
                'supplier_id' => $request['supplier_id'] ?? null,
                'description' => $request['description'] ?? '',
                'has_variant' => $request['has_variant'] ?? 0,
                'type'        => $request['type'] ?? 'single',
                'image_cover' => $coverPath ? '/storage/' . $coverPath : null,
                'status'      => 'pending'
            ];
            $attributes = $request['variants'] ?? [];
            if (empty($attributes)) {
                throw new Exception("Vui lòng chọn ít nhất 1 biến thể !");
            }
            $stocks = json_decode($request['stock_data'], true);
            if (empty($stocks)) {
                throw new Exception("Vui lòng chọn ít nhất 1 kho !");
            }
            $product = Product::findOrFail($id);
            if (!$product) {
                throw new Exception("Không tìm thấy sản phẩm !");
            }
            $result = $product->update($data);

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

            if ($result) {
                $stockData = [];
                foreach ($stocks as $key => $stock) {
                    $fillStock = [
                        'id'                   => $stock['id'] ?? null,
                        'product_id'           => $id,
                        'quantity'             => $stock['quantity'] ?? 0,
                        'max_discount_percent' => $stock['max_discount_percent'] ?? 0,
                        'max_increase_percent' => $stock['max_increase_percent'] ?? 0,
                        'auto_calc'            => $stock['auto_calc'] ?? 1
                    ];
                    if ($data['type'] !== 'variable') {
                        $fillStock = array_merge($fillStock, ['sell_price' => 0, 'purchase_price' => 0]);
                    } else {
                        $fillStock = array_merge($fillStock, ['sell_price' => 0, 'purchase_price' => 0]);
                    }
                    $stockData[] = $fillStock;
                }

                batch(new StockProduct(), array_filter($stockData, function ($item) {
                    return !is_null($item['id']);
                }), 'id');

                StockProduct::insert(array_filter($stockData, function ($item) {
                    return is_null($item['id']);
                }));

                StockProduct::where('product_id', $id)
                    ->where('product_type', 'root_stock')
                    ->whereNotIn('id', array_column($stockData, 'id'))
                    ->delete();

                if ($data['type'] === 'variable') {
                    $canceledVariant = [];
                    foreach ($attributes as $key => $attribute) {
                        // Xử lý SKU
                        $sku = $attribute['sku'] ?: $this->generateUniqueSku($request->input('sku') ?? 'SP', $attribute['id'] ?? null);
                        // Chuẩn bị dữ liệu cập nhật
                        $fillAttribute = [
                            'id'                   => $attribute['id'] ?? null,
                            'product_id'           => $id,
                            'stock_id'             => $attribute['stock_id'] ?? null,
                            'quantity'             => $attribute['quantity'] ?? 0,
                            'sell_price'           => $attribute['sell_price'] ?? 0,
                            'purchase_price'       => $attribute['purchase_price'] ?? 0,
                            'sku'                  => $sku,
                            'barcode'              => $attribute['barcode'] ?? '',
                            'is_sale'              => $attribute['is_sale'] ?? 1,
                            'max_discount_percent' => $attribute['max_discount_percent'] ?? 0,
                            'max_increase_percent' => $attribute['max_increase_percent'] ?? 0,
                            'auto_calc'            => $attribute['auto_calc'] ?? 1,
                            'is_using'             => $attribute['is_using'] ?? 1,
                            'attribute_first_id'   => $attribute['attributes'][0]['value_id'] ?? null,
                            'attribute_second_id'  => $attribute['attributes'][1]['value_id'] ?? null,
                            'product_type'         => 'variable',
                            'is_product_variant'   => 1
                        ];

                        // Tạo mới hoặc cập nhật
                        $attributeModel = is_null($fillAttribute['id'])
                            ? StockProduct::create($fillAttribute)
                            : tap(StockProduct::findOrFail($fillAttribute['id']))->update($fillAttribute);

                        if (!$attributeModel) {
                            throw new \Exception("Lỗi khi thêm biến thể!");
                        }

                        $canceledVariant[] = $attributeModel->id;

                        // Xử lý ảnh nếu có
                        if ($request->hasFile("variants.$key.image")) {
                            $file = $request->file("variants.$key.image");
                            $path = $file->store('products/variants', 'public');

                            ProductVariantImage::create([
                                'stock_product_id' => $attributeModel->id,
                                'image'            => '/storage/' . $path,
                            ]);
                        }
                    }

                    // Xóa các biến thể không còn sử dụng
                    if (!empty($canceledVariant)) {
                        StockProduct::where('product_id', $id)
                            ->where('product_type', 'variable')
                            ->whereNotIn('id', $canceledVariant)
                            ->delete();
                    }
                }
            }

            DB::commit();
            return response()->json(['message' => 'Chỉnh sửa sản phẩm thành công!'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
