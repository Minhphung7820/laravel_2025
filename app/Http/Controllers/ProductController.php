<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductAttributesResource;
use App\Http\Resources\ProductComboResource;
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

        if ($search = $request->input('keyword')) {
            $query->where(function ($q) use ($search) {
                $q->where('p.name', 'like', "%{$search}%")
                    ->orWhere('st.sku', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('p.status', $status);
        }

        $query->select([
            'st.id',
            'st.product_id',
            DB::raw("CASE WHEN p.type = 'variable' THEN st.sku ELSE p.sku END AS sku"),
            'p.name as product_name',
            's.name as stock_name',
            'p.type as product_type',
            'st.purchase_price',
            'st.sell_price',
            'st.quantity',
            'u.name as unit_name',
            DB::raw("CASE
                WHEN p.type = 'variable' THEN
                    COALESCE(CONCAT('$urlPrefix', pvi.image), '" . env('IMAGE_DEFAULT') . "')
                ELSE
                    COALESCE(CONCAT('$urlPrefix', p.image_cover), '" . env('IMAGE_DEFAULT') . "')
            END AS image"),
            'p.status',
        ]);

        $query->leftJoin('product_variant_images as pvi', function ($join) {
            $join->on('pvi.stock_product_id', '=', 'st.id');
        });

        $query->orderByRaw("
            CASE
                WHEN p.type = 'variable' THEN st.created_at
                ELSE p.created_at
            END DESC
        ");

        $results = $query->paginate($limit);

        return $this->responseSuccess($results);
    }

    public function getInitCombo(Request $request)
    {
        $limit = $request->input('limit', 20);
        $search = $request->input('keyword');
        $urlPrefix = url('/');
        $imageDefault = env('IMAGE_DEFAULT');

        $query = \App\Models\StockProduct::with([
            'product:id,type,status',
            'product.stockData',
            'product.stockData.stock:id,name',
            'attributeFirst:id,title',
            'attributeSecond:id,title',
        ])
            ->select([
                'stock_products.id',
                'stock_products.product_id',
                DB::raw("CASE WHEN products.type = 'variable' THEN stock_products.sku ELSE products.sku END AS sku"),
                'products.name as product_name',
                'stocks.name as stock_name',
                'stocks.id as stock_id',
                'products.type as product_type',
                'stock_products.purchase_price',
                'stock_products.sell_price',
                'stock_products.quantity',
                'stock_products.attribute_first_id',
                'stock_products.attribute_second_id',
                'units.name as unit_name',
                DB::raw("CASE
                    WHEN products.type = 'variable' THEN
                        COALESCE(CONCAT('$urlPrefix', product_variant_images.image), '$imageDefault')
                    ELSE
                        COALESCE(CONCAT('$urlPrefix', products.image_cover), '$imageDefault')
                END AS image"),
                'products.status',
                DB::raw('(SELECT JSON_ARRAYAGG(JSON_OBJECT(
                    "id", sp2.id,
                    "product_id", sp2.product_id,
                    "attribute_first_id", sp2.attribute_first_id,
                    "attribute_second_id", sp2.attribute_second_id,
                    "sell_price", sp2.sell_price,
                    "purchase", sp2.sell_price,
                    "stock_name", s.name,
                    "attr1_title", attr1.title,
                    "attr2_title", attr2.title,
                    "quantity", sp2.quantity,
                    "stock_id", s.id,
                    "is_stock_default", s.is_default,
                    "sku", sp2.sku
                ))
                FROM stock_products sp2
                JOIN stocks as s ON sp2.stock_id = s.id
                LEFT JOIN attributes as attr1 ON sp2.attribute_first_id = attr1.id
                LEFT JOIN attributes as attr2 ON sp2.attribute_second_id = attr2.id
                WHERE sp2.product_id = stock_products.product_id
                AND (
                    (sp2.attribute_first_id IS NULL AND stock_products.attribute_first_id IS NULL)
                    OR sp2.attribute_first_id = stock_products.attribute_first_id
                )
                AND (
                    (sp2.attribute_second_id IS NULL AND stock_products.attribute_second_id IS NULL)
                    OR sp2.attribute_second_id = stock_products.attribute_second_id
                )
                AND sp2.product_type = "variable"
                ) AS related_variants')
            ])
            ->join('products', 'stock_products.product_id', '=', 'products.id')
            ->join('stocks', function ($join) {
                $join->on('stock_products.stock_id', '=', 'stocks.id')
                    ->where('stocks.is_default', 1);
            })
            ->leftJoin('units', 'stock_products.unit_id', '=', 'units.id')
            ->leftJoin('product_variant_images', 'product_variant_images.stock_product_id', '=', 'stock_products.id')
            ->where(function ($q) use ($request) {
                $q->where(function ($q2) use ($request) {
                    $q2->where('products.type', 'variable')
                        ->where('stock_products.product_type', 'variable')
                        ->where('stock_products.is_using', 1)
                        ->where('stock_products.is_sale', 1)
                        ->when(!empty($request['excepts_variable']), function ($q) use ($request) {
                            return $q->whereNotIn('stock_products.id', explode(",", $request['excepts_variable']));
                        });
                })->orWhere(function ($q2) use ($request) {
                    $q2->whereIn('products.type', ['single'])
                        ->where('stock_products.product_type', 'root_stock')
                        ->when(!empty($request['excepts_single']), function ($q) use ($request) {
                            return $q->whereNotIn('stock_products.id', explode(",", $request['excepts_single']));
                        });
                });
            });
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('products.name', 'like', "%$search%")
                    ->orWhere('stock_products.sku', 'like', "%$search%");
            });
        }
        $query->orderByRaw("
            CASE
                WHEN products.type = 'variable' THEN stock_products.created_at
                ELSE products.created_at
            END DESC
        ");
        return $this->responseSuccess($query->paginate($limit));
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
            return $this->responseSuccess($recommend);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
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
                $coverPath = $this->uploadFile($request->file('cover_image'), 'products/cover');
            }
            // Tạo sản phẩm chính
            $product = Product::create([
                'name'         => $request->input('name') ?? null,
                'sku'          => $request->input('sku') ?? null,
                'barcode'      => $request->input('barcode') ?? null,
                'type'         => $request->input('type') ?? null,
                'has_variant'  => $request->input('has_variant') ?? 0,
                'has_serial'   => $request->input('has_serial') ?? 0,
                'warranty'     => $request->input('warranty') ?? null,
                'unit_id'      => $request->input('unit_id') ?? null,
                'brand_id'     => $request->input('brand_id') ?? null,
                'category_id'  => $request->input('category_id') ?? null,
                'supplier_id'  => $request->input('supplier_id') ?? null,
                'image_cover'  => $coverPath ? $coverPath : null,
                'status'       => 'pending',
            ]);
            // Lưu ảnh chính (gallery_images) hàng loạt
            if ($request->hasFile('gallery_images')) {
                $imagesData = [];
                foreach ($request->file('gallery_images') as $file) {
                    $path = $this->uploadFile($file, 'products/gallery');
                    $imagesData[] = [
                        'product_id' => $product->id,
                        'image'      => $path,
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
                        'stock_id'             => $item['stock_id'],
                        'product_id'           => $product->id,
                        'quantity'             => $item['quantity'] ?? 0,
                        'purchase_price'       => $item['purchase_price'] ?? 0,
                        'sell_price'           => $item['sell_price'] ?? 0,
                        'max_discount_percent' => $item['max_discount_percent'] ?? 0,
                        'max_increase_percent' => $item['max_increase_percent'] ?? 0,
                        'auto_calc'            => $item['auto_calc'] ?? false,
                        'is_product_variant'   => 0,
                        'product_type'         => 'root_stock',
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
                        $path = $this->uploadFile($file, 'products/variants');
                        ProductVariantImage::create([
                            'stock_product_id' => $variantStock->id,
                            'image'            => $path,
                        ]);
                    }
                }
            }
            if ($request->input('type') === 'combo') {
                $combos = json_decode($request['combo'], true) ?? [];
                $combosPrepare = $this->getPrepareCombo($product['id'], $combos);
                $syncCombo = $this->syncProductsCombo($product['id'], $combosPrepare);
                if (! $syncCombo) {
                    DB::rollBack();
                    return $this->responseError(__('common.product.save_combo_failed'), 500);
                }
            }
            DB::commit();
            return $this->responseSuccess(true, __('common.product.create_success'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseError(__('common.product.create_failed'), 500);
        }
    }

    private function getPrepareCombo($productId, $arrays = [])
    {
        return array_map(function ($item) use ($productId) {
            return [
                'id'                  => $item['id'],
                'product_id'          => $productId,
                'stock_id'            => $item['stock_id'],
                'product_type'        => 'combo',
                'attribute_first_id'  => $item['attribute_first_id'],
                'attribute_second_id' => $item['attribute_second_id'],
                'parent_id'           => $item['parent_id'],
                'sell_price_combo'    => $item['sell_price_combo'],
                'quantity_combo'      => $item['quantity_combo']
            ];
        }, $arrays);
    }

    private function syncProductsCombo($productId, $products = [])
    {
        try {
            $inserts = [];
            $updates = [];
            foreach ($products as $key => $product) {
                if (is_null($product['id'])) {
                    unset($product['id']);
                    $inserts[] = StockProduct::create($product)
                        ->id;
                } else {
                    $updates[] = $product;
                }
            }
            if (!empty($updates)) {
                batch()->update(new StockProduct(), array_map(function ($item) {
                    $item['updated_at'] = now();
                    return $item;
                }, $updates), 'id');
            }
            StockProduct::where('product_type', 'combo')
                ->where('product_id', $productId)
                ->whereNotIn('id', array_merge($inserts, array_column($updates, 'id')))
                ->delete();
            return true;
        } catch (Exception $e) {
            throw new Exception($e);
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
        try {
            $product = Product::findOrFail($id);
            $baseRelations = ['galleryImages', 'stockData.stock'];

            switch ($product->type) {
                case 'variable':
                    $product->load(array_merge($baseRelations, [
                        'attributes.attributeFirst.variant',
                        'attributes.attributeSecond.variant',
                        'attributes.variantImages',
                    ]));

                    return $this->responseSuccess([
                        'product'        => $product,
                        'stock_products' => ProductAttributesResource::collection($product->attributes ?? []),
                    ]);

                case 'combo':
                    $product->load(array_merge($baseRelations, [
                        'combo.parent',
                        'combo.parent.attributeFirst:id,title',
                        'combo.parent.attributeSecond:id,title',
                        'combo.parent.product.stockData.stock:id,name',
                        'combo.parent.variantImages:id,stock_product_id,image',
                        'combo.parent.product:id,type,status,image_cover,name,sku',
                    ]));

                    return $this->responseSuccess([
                        'product' => $product,
                        'combo'   => ProductComboResource::collection($product->combo ?? []),
                    ]);

                case 'single':
                default:
                    $product->load($baseRelations);

                    return $this->responseSuccess([
                        'product' => $product,
                    ]);
            }
        } catch (\Exception $e) {
            return $this->responseError('Không tìm thấy sản phẩm', 404, $e->getMessage());
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
        DB::beginTransaction();
        try {
            $coverPath = null;
            if ($request->hasFile('cover_image')) {
                $coverPath = $this->uploadFile($request->file('cover_image'), 'products/cover');
            }
            $data = [
                'name'        => $request['name'],
                'sku'         => $request['sku'] ?? '',
                'barcode'     => $request['barcode'] ?? '',
                'has_serial'  => $request['has_serial'] ?? 0,
                'warranty'    => $request['warranty'] ?? null,
                'unit_id'     => $request['unit_id'] ?? null,
                'brand_id'    => $request['brand_id'] ?? null,
                'category_id' => $request['category_id'] ?? null,
                'supplier_id' => $request['supplier_id'] ?? null,
                'description' => $request['description'] ?? '',
                'has_variant' => $request['has_variant'] ?? 0,
                'type'        => $request['type'] ?? 'single',
                'status'      => 'pending'
            ];
            if ($coverPath) {
                $data['image_cover'] =  $coverPath;
            }
            if ($data['type'] === 'variable') {
                $attributes = $request['variants'] ?? [];
                if (empty($attributes)) {
                    throw new Exception(__('common.product.missing_variant'));
                }
            }
            $stocks = json_decode($request['stock_data'], true);
            if (empty($stocks)) {
                throw new Exception(__('common.product.missing_stock'));
            }
            $product = Product::findOrFail($id);
            if (!$product) {
                throw new Exception(__('common.product.not_found'));
            }
            if ($product['type'] === 'variable' && $data['type'] === 'single') {
                $data['has_variant'] = 0;
                StockProduct::where('product_id', $product['id'])
                    ->where('product_type', 'variable')
                    ->delete();
            }
            $result = $product->update($data);
            $deletedIds = $request->input('deleted_gallery_ids', []);

            if (is_array($deletedIds) && count($deletedIds) > 0) {
                $validIds = array_filter($deletedIds, fn($id) => is_numeric($id));
                ProductImage::whereIn('id', $validIds)->delete();
            }

            if ($request->hasFile('gallery_images')) {
                $imagesData = [];
                foreach ($request->file('gallery_images') as $file) {
                    $path = $this->uploadFile($file, 'products/gallery');
                    $imagesData[] = [
                        'product_id' => $product->id,
                        'image'      => $path,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                ProductImage::insert($imagesData);
            }

            if ($result) {
                $stockData = [];

                foreach ($stocks as $stock) {
                    $stockData[] = [
                        'id'                   => $stock['id'] ?? null,
                        'product_id'           => $id,
                        'stock_id'             => $stock['stock_id'] ?? null,
                        'quantity'             => $stock['quantity'] ?? 0,
                        'max_discount_percent' => $stock['max_discount_percent'] ?? 0,
                        'max_increase_percent' => $stock['max_increase_percent'] ?? 0,
                        'auto_calc'            => $stock['auto_calc'] ?? 1,
                        'product_type'         => 'root_stock',
                        'sell_price'           => $stock['sell_price'] ?? 0,
                        'purchase_price'       => $stock['purchase_price'] ?? 0,
                    ];
                }

                batch()->update(new StockProduct(), array_filter(array_map(function ($item) {
                    $item['updated_at'] = now();
                    return $item;
                }, $stockData), function ($item) {
                    return !is_null($item['id']);
                }), 'id');

                StockProduct::insert(array_filter(array_map(function ($item) {
                    $item['created_at'] = now();
                    return $item;
                }, $stockData), function ($item) {
                    return is_null($item['id']);
                }));

                if (!empty($request['remove_stock_ids'])) {
                    StockProduct::where('product_id', $id)
                        ->where('product_type', 'root_stock')
                        ->whereIn('stock_id', $request['remove_stock_ids'])
                        ->delete();
                }

                if ($data['type'] === 'variable') {
                    $canceledVariant      = [];
                    $canceledImageVariant = [];
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
                            throw new Exception(__('common.product.variant_create_failed'));
                        }
                        $canceledVariant[] = $attributeModel->id;
                        // Xử lý ảnh nếu có
                        if ($request->hasFile("variants.$key.image")) {
                            $file = $request->file("variants.$key.image");
                            $path = $this->uploadFile($file, 'products/variants');
                            $newImageAttr = ProductVariantImage::create([
                                'stock_product_id' => $attributeModel->id,
                                'image'            => $path,
                            ]);
                            if ($newImageAttr) {
                                $canceledImageVariant[]  = [
                                    'id'                 => $newImageAttr['id'],
                                    'stock_product_id'   => $attributeModel->id
                                ];
                            }
                        }
                    }
                    if (!empty($canceledImageVariant)) {
                        ProductVariantImage::whereIn('stock_product_id', array_column($canceledImageVariant, 'stock_product_id'))
                            ->whereNotIn('id', array_column($canceledImageVariant, 'id'))
                            ->delete();
                    }
                    // Xóa các biến thể không còn sử dụng
                    if (!empty($canceledVariant)) {
                        StockProduct::where('product_id', $id)
                            ->where('product_type', 'variable')
                            ->whereNotIn('id', $canceledVariant)
                            ->delete();
                    }
                }
                if ($data['type'] === 'combo') {
                    $combos = json_decode($request['combo'], true) ?? [];
                    $combosPrepare = $this->getPrepareCombo($product['id'], $combos);
                    $syncCombo = $this->syncProductsCombo($product['id'], $combosPrepare);
                    if (! $syncCombo) {
                        DB::rollBack();
                        return $this->responseError(__('common.product.save_combo_failed'));
                    }
                }
            }

            DB::commit();
            return $this->responseSuccess(true, __('common.product.update_success'));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseError(__('common.product.update_failed'), 500);
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
