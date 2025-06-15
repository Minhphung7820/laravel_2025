<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductAttributesResource;
use App\Http\Resources\ProductComboResource;
use App\Models\Attribute;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariantImage;
use App\Models\StockProduct;
use App\Models\Variant;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $limit = $request->input('limit', 20);
        $urlPrefix = url('/');

        $query = Product::with([
            'comboList.parent.attributeFirst:id,title',
            'comboList.parent.attributeSecond:id,title',
            'comboList.parent.product:id,name,image_cover,type,sku',
            'comboList.parent.variantImages:id,image,stock_product_id',
            'comboList:id,sell_price_combo,parent_id,quantity_combo,product_id',
            'comboList.parent:id,sku,product_id,attribute_first_id,attribute_second_id'
        ]);
        $this->applyCommonProductFilters($query, $request, true)
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftJoin('categories as cat', 'products.category_id', '=', 'cat.id')
            ->leftJoin('customers as splr', 'products.supplier_id', '=', 'splr.id')
            ->leftJoin('units as u', 'products.unit_id', '=', 'u.id')
            ->leftJoin('attributes as attr1', 'st.attribute_first_id', '=', 'attr1.id')
            ->leftJoin('attributes as attr2', 'st.attribute_second_id', '=', 'attr2.id')
            ->leftJoin('variants as var1', 'attr1.variant_id', '=', 'var1.id')
            ->leftJoin('variants as var2', 'attr2.variant_id', '=', 'var2.id')
            ->leftJoin('product_variant_images as pvi', 'pvi.stock_product_id', '=', 'st.id');

        $query->select([
            'st.id',
            'st.product_id',
            DB::raw("CASE WHEN products.type = 'variable' THEN st.sku ELSE products.sku END AS sku"),
            DB::raw("CASE
                WHEN products.type = 'variable' THEN
                    CONCAT(
                        products.name,
                        ' ',
                        COALESCE(var1.title, ''), ' ',
                        COALESCE(attr1.title, ''), ' ',
                        COALESCE(var2.title, ''), ' ',
                        COALESCE(attr2.title, '')
                    )
                ELSE products.name
            END AS product_name"),
            's.name as stock_name',
            'products.type as product_type',
            'st.purchase_price',
            DB::raw("CASE
                WHEN products.type = 'combo' THEN st.sell_price_combo
                ELSE st.sell_price
            END AS sell_price"),
            'st.quantity',
            'u.name as unit_name',
            DB::raw("CASE
                WHEN products.type = 'variable' THEN
                    COALESCE(CONCAT('$urlPrefix', pvi.image), '" . env('IMAGE_DEFAULT') . "')
                ELSE
                    COALESCE(CONCAT('$urlPrefix', products.image_cover), '" . env('IMAGE_DEFAULT') . "')
            END AS image"),
            'products.status',
        ]);

        $query->orderBy("st.id", "desc");

        $results = $query->paginate($limit);

        return $this->responseSuccess($results);
    }

    public function getStatusCounts(Request $request)
    {
        $query = Product::query()
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftJoin('categories as cat', 'products.category_id', '=', 'cat.id')
            ->leftJoin('customers as splr', 'products.supplier_id', '=', 'splr.id')
            ->leftJoin('units as u', 'products.unit_id', '=', 'u.id');
        $this->applyCommonProductFilters($query, $request);

        $query->select('products.status', DB::raw('COUNT(*) as total'))
            ->groupBy('products.status');

        $rawCounts = $query->get()->pluck('total', 'status')->toArray();

        $counts = [
            [
                'key'   => 'all',
                'label' => 'product_status.all',
                'count' => array_sum($rawCounts)
            ],
            [
                'key'   => 'pending',
                'label' => 'product_status.pending',
                'count' => $rawCounts['pending'] ?? 0
            ],
            [
                'key'   => 'approved',
                'label' => 'product_status.approved',
                'count' => $rawCounts['approved'] ?? 0
            ],
            [
                'key'   => 'rejected',
                'label' => 'product_status.rejected',
                'count' => $rawCounts['rejected'] ?? 0
            ]
        ];

        return $this->responseSuccess($counts);
    }

    private function applyCommonProductFilters($query, Request $request, $statusFilter = false)
    {
        $query->join('stock_products as st', 'products.id', '=', 'st.product_id')
            ->join('stocks as s', function ($join) {
                $join->on('st.stock_id', '=', 's.id')
                    ->where('s.is_default', 1);
            })
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->where('products.type', 'variable')
                        ->where('st.product_type', 'variable');
                })->orWhere(function ($q2) {
                    $q2->whereIn('products.type', ['single', 'combo'])
                        ->where('st.product_type', 'root_stock');
                });
            });

        // Search
        if ($search = $request->input('keyword')) {
            $query->where(function ($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                    ->orWhere('st.sku', 'like', "%{$search}%");
            });
        }

        // Status
        if ($statusFilter && $request->filled('status')) {
            $query->where('products.status', $request->input('status'));
        }

        // Supplier
        if ($supplier = $request->input('supplier')) {
            $query->where('splr.name', 'like', "%{$supplier}%");
        }

        // Brand
        if ($brand = $request->input('brand')) {
            $query->where('brands.name', 'like', "%{$brand}%");
        }

        // Category
        if ($category = $request->input('category')) {
            $query->where('cat.title', 'like', "%{$category}%");
        }

        // Unit
        if ($unit = $request->input('unit')) {
            $query->where('u.name', 'like', "%{$unit}%");
        }

        // From Date
        if ($fromDate = $request->input('from_date')) {
            $query->whereDate('products.created_at', '>=', $fromDate);
        }

        // To Date
        if ($toDate = $request->input('to_date')) {
            $query->whereDate('products.created_at', '<=', $toDate);
        }

        // Time Range (override From/To date)
        if ($timeRange = $request->input('time_range')) {
            $now = now();
            switch ($timeRange) {
                case 'today':
                    $query->whereDate('products.created_at', $now->toDateString());
                    break;
                case 'this_week':
                    $query->whereBetween('products.created_at', [$now->startOfWeek(), $now->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereBetween('products.created_at', [$now->startOfMonth(), $now->endOfMonth()]);
                    break;
            }
        }

        return $query;
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
            ->leftJoin('attributes as attr1_org', 'stock_products.attribute_first_id', '=', 'attr1_org.id')
            ->leftJoin('attributes as attr2_org', 'stock_products.attribute_second_id', '=', 'attr2_org.id')
            ->leftJoin('variants as var1', 'attr1_org.variant_id', '=', 'var1.id')
            ->leftJoin('variants as var2', 'attr2_org.variant_id', '=', 'var2.id')
            ->join('products', 'stock_products.product_id', '=', 'products.id')
            ->join('stocks', 'stock_products.stock_id', '=', 'stocks.id')
            ->leftJoin('units as u', 'products.unit_id', '=', 'u.id')
            ->leftJoin('product_variant_images', 'product_variant_images.stock_product_id', '=', 'stock_products.id')
            ->where('stocks.is_default', 1)
            ->select([
                'stock_products.id',
                'stock_products.product_id',
                DB::raw("CASE
                    WHEN products.type = 'variable' THEN stock_products.sku
                    ELSE products.sku
                    END AS sku"),
                DB::raw("CASE
                    WHEN products.type = 'variable' THEN
                        CONCAT(
                            products.name,
                            ' ',
                            COALESCE(var1.title, ''), ' ',
                            COALESCE(attr1_org.title, ''), ' ',
                            COALESCE(var2.title, ''), ' ',
                            COALESCE(attr2_org.title, '')
                        )
                    ELSE products.name
                END AS product_name"),
                // 'products.name as product_name',
                'stocks.name as stock_name',
                'stocks.id as stock_id',
                'products.type as product_type',
                'stock_products.purchase_price',
                'stock_products.sell_price',
                'stock_products.quantity',
                'stock_products.attribute_first_id',
                'stock_products.attribute_second_id',
                'u.name as unit_name',
                DB::raw("CASE
                    WHEN products.type = 'variable' THEN
                        COALESCE(CONCAT('$urlPrefix', product_variant_images.image), '$imageDefault')
                    ELSE
                        COALESCE(CONCAT('$urlPrefix', products.image_cover), '$imageDefault')
                END AS image"),
                'products.status',
                DB::raw('(
                SELECT JSON_ARRAYAGG(JSON_OBJECT(
                    "id", sp2.id,
                    "product_id", sp2.product_id,
                    "attribute_first_id", sp2.attribute_first_id,
                    "attribute_second_id", sp2.attribute_second_id,
                    "sell_price", sp2.sell_price,
                    "purchase_price", sp2.purchase_price,
                    "stock_name", s.name,
                    "attr1_title", attr1.title,
                    "attr2_title", attr2.title,
                    "quantity", sp2.quantity,
                    "stock_id", s.id,
                    "is_stock_default", s.is_default,
                    "sku", sp2.sku,
                    "image", COALESCE(CONCAT("' . $urlPrefix . '", pvi.image), "' . $imageDefault . '")
                ))
                FROM stock_products sp2
                JOIN stocks s ON sp2.stock_id = s.id
                LEFT JOIN attributes attr1 ON sp2.attribute_first_id = attr1.id
                LEFT JOIN attributes attr2 ON sp2.attribute_second_id = attr2.id

                LEFT JOIN (
                    SELECT t1.*
                    FROM product_variant_images t1
                    INNER JOIN (
                        SELECT stock_product_id, MIN(id) AS min_id
                        FROM product_variant_images
                        GROUP BY stock_product_id
                    ) t2 ON t1.stock_product_id = t2.stock_product_id AND t1.id = t2.min_id
                ) AS pvi ON pvi.stock_product_id = sp2.id

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
        $query->orderBy('stock_products.id', 'desc');
        return $this->responseSuccess($query->paginate($limit));
    }

    public function getAllProducts(Request $request)
    {
        $search = $request->input('keyword');
        $urlPrefix = url('/');
        $imageDefault = env('IMAGE_DEFAULT');
        $exceptIds = explode(",", $request['except_ids'] ?? '');
        $query = \App\Models\StockProduct::with([
            'product:id,type',
            'product.combo.parent.attributeFirst:id,title,variant_id',
            'product.combo.parent.attributeSecond:id,title,variant_id',
            'product.combo.parent.attributeFirst.variant:id,title',
            'product.combo.parent.attributeSecond.variant:id,title',
            'product.combo.parent.product:id,name,image_cover,type,sku',
            'product.combo.parent.variantImages:id,image,stock_product_id',
            'product.combo:id,sell_price_combo,parent_id,quantity_combo,product_id',
            'product.combo.parent:id,sku,product_id,attribute_first_id,attribute_second_id'
        ])
            ->leftJoin('attributes as attr1_org', 'stock_products.attribute_first_id', '=', 'attr1_org.id')
            ->leftJoin('attributes as attr2_org', 'stock_products.attribute_second_id', '=', 'attr2_org.id')
            ->leftJoin('variants as var1', 'attr1_org.variant_id', '=', 'var1.id')
            ->leftJoin('variants as var2', 'attr2_org.variant_id', '=', 'var2.id')
            ->join('products', 'stock_products.product_id', '=', 'products.id')
            ->join('stocks', 'stock_products.stock_id', '=', 'stocks.id')
            ->leftJoin('units as u', 'products.unit_id', '=', 'u.id')
            ->leftJoin('product_variant_images', 'product_variant_images.stock_product_id', '=', 'stock_products.id')
            ->where('stocks.is_default', 1)
            ->select([
                'stock_products.id',
                'stock_products.product_id',
                DB::raw("CASE
                    WHEN products.type = 'variable' THEN stock_products.sku
                    ELSE products.sku
                    END AS sku"),
                DB::raw("CASE
                    WHEN products.type = 'variable' THEN
                        CONCAT(
                            products.name,
                            ' ',
                            COALESCE(var1.title, ''), ' ',
                            COALESCE(attr1_org.title, ''), ' ',
                            COALESCE(var2.title, ''), ' ',
                            COALESCE(attr2_org.title, '')
                        )
                    ELSE products.name
                END AS product_name"),
                'stocks.name as stock_name',
                'stocks.id as stock_id',
                'products.type as product_type',
                'stock_products.purchase_price',
                'stock_products.purchase_price as purchase_price_main',
                DB::raw("CASE
                    WHEN products.type = 'combo' THEN stock_products.sell_price_combo
                    ELSE stock_products.sell_price
                END AS sell_price"),
                DB::raw("CASE
                    WHEN products.type = 'combo' THEN stock_products.sell_price_combo
                    ELSE stock_products.sell_price
                END AS sell_price_main"),
                'stock_products.quantity',
                'stock_products.attribute_first_id',
                'stock_products.attribute_second_id',
                'u.name as unit_name',
                DB::raw("CASE
                    WHEN products.type = 'variable' THEN
                        COALESCE(CONCAT('$urlPrefix', product_variant_images.image), '$imageDefault')
                    ELSE
                        COALESCE(CONCAT('$urlPrefix', products.image_cover), '$imageDefault')
                END AS image"),
                'products.status',
                DB::raw('(
                    CASE
                        WHEN products.type = "variable" THEN (
                            SELECT JSON_ARRAYAGG(JSON_OBJECT(
                                \'id\', sp2.id,
                                \'product_id\', sp2.product_id,
                                \'attribute_first_id\', sp2.attribute_first_id,
                                \'attribute_second_id\', sp2.attribute_second_id,
                                \'sell_price\', (CASE WHEN products.type = \'combo\' THEN sp2.sell_price_combo ELSE sp2.sell_price END),
                                \'purchase_price\', sp2.purchase_price,
                                \'sell_price_main\', (CASE WHEN products.type = \'combo\' THEN sp2.sell_price_combo ELSE sp2.sell_price END),
                                \'purchase_price_main\', sp2.purchase_price,
                                \'stock_name\', s.name,
                                \'attr1_title\', attr1.title,
                                \'attr2_title\', attr2.title,
                                \'quantity\', sp2.quantity,
                                \'stock_id\', s.id,
                                \'is_stock_default\', s.is_default,
                                \'sku\', sp2.sku,
                                \'image\', COALESCE(CONCAT(\'' . $urlPrefix . '\', pvi.image), \'' . $imageDefault . '\')
                            ))
                            FROM stock_products sp2
                            JOIN stocks s ON sp2.stock_id = s.id
                            LEFT JOIN attributes attr1 ON sp2.attribute_first_id = attr1.id
                            LEFT JOIN attributes attr2 ON sp2.attribute_second_id = attr2.id
                            LEFT JOIN (
                                SELECT t1.*
                                FROM product_variant_images t1
                                INNER JOIN (
                                    SELECT stock_product_id, MIN(id) AS min_id
                                    FROM product_variant_images
                                    GROUP BY stock_product_id
                                ) t2 ON t1.stock_product_id = t2.stock_product_id AND t1.id = t2.min_id
                            ) AS pvi ON pvi.stock_product_id = sp2.id
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
                        )
                        ELSE (
                            SELECT JSON_ARRAYAGG(JSON_OBJECT(
                                \'id\', sp2.id,
                                \'product_id\', sp2.product_id,
                                \'attribute_first_id\', sp2.attribute_first_id,
                                \'attribute_second_id\', sp2.attribute_second_id,
                                \'sell_price\', (CASE WHEN products.type = \'combo\' THEN sp2.sell_price_combo ELSE sp2.sell_price END),
                                \'purchase_price\', sp2.purchase_price,
                                \'sell_price_main\', (CASE WHEN products.type = \'combo\' THEN sp2.sell_price_combo ELSE sp2.sell_price END),
                                \'purchase_price_main\', sp2.purchase_price,
                                \'stock_name\', s.name,
                                \'attr1_title\', NULL,
                                \'attr2_title\', NULL,
                                \'quantity\', sp2.quantity,
                                \'stock_id\', s.id,
                                \'is_stock_default\', s.is_default,
                                \'sku\', sp2.sku,
                                \'image\', COALESCE(CONCAT(\'' . $urlPrefix . '\', p.image_cover), \'' . $imageDefault . '\')
                            ))
                            FROM stock_products sp2
                            JOIN stocks s ON sp2.stock_id = s.id
                            JOIN products p ON sp2.product_id = p.id
                            WHERE sp2.product_id = stock_products.product_id
                            AND sp2.parent_id IS NULL
                            AND sp2.product_type != "variable"
                        )
                    END
                ) AS stocks')

            ])
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
                    $q2->whereIn('products.type', ['single', 'combo'])
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

        if (!empty($exceptIds)) {
            $query->whereNotIn('stock_products.id', $exceptIds);
        }

        $query->orderBy('stock_products.id', 'desc');

        return $query->paginate($request['limit'] ?? 10);
    }

    public function getProductsByTypeOrderPriceQuote($request)
    {
        $search = $request['keyword'] ?? '';
        $customerId = $request['customer_id'] ?? '';
        $transactionType = $request['transaction_type'];
        $urlPrefix = url('/');
        $imageDefault = env('IMAGE_DEFAULT');
        $exceptIds = explode(",", $request['except_ids'] ?? '');

        $query = \App\Models\StockProduct::with([
            'product:id,type',
            'product.combo.parent.attributeFirst:id,title,variant_id',
            'product.combo.parent.attributeSecond:id,title,variant_id',
            'product.combo.parent.attributeFirst.variant:id,title',
            'product.combo.parent.attributeSecond.variant:id,title',
            'product.combo.parent.product:id,name,image_cover,type,sku',
            'product.combo.parent.variantImages:id,image,stock_product_id',
            'product.combo:id,sell_price_combo,parent_id,quantity_combo,product_id',
            'product.combo.parent:id,sku,product_id,attribute_first_id,attribute_second_id'
        ])
            ->join('products', 'stock_products.product_id', '=', 'products.id')
            ->leftJoin('units as u', 'products.unit_id', '=', 'u.id')
            ->leftJoin('product_variant_images', 'product_variant_images.stock_product_id', '=', 'stock_products.id')
            ->leftJoin(
                DB::raw(
                    "(
                SELECT oi1.*
                FROM order_items oi1
                INNER JOIN (
                    SELECT oi.stock_product_id, MAX(o.transaction_date) AS max_date
                    FROM order_items oi
                    JOIN orders o ON o.id = oi.order_id
                    WHERE o.status = 'approved'
                    AND o.customer_id = $customerId
                    AND o.transaction_type = '$transactionType'
                    GROUP BY oi.stock_product_id
                ) latest ON latest.stock_product_id = oi1.stock_product_id
                    JOIN orders o2 ON o2.id = oi1.order_id AND o2.transaction_date = latest.max_date
                    WHERE o2.status = 'approved'
                    AND o2.customer_id = $customerId
                    AND o2.transaction_type = '$transactionType'
                ) AS last_order_items"
                ),
                'last_order_items.stock_product_id',
                '=',
                'stock_products.id'
            )
            ->leftJoin('stocks', 'stocks.id', '=', DB::raw('last_order_items.stock_id'))
            ->leftJoin('attributes as attr1_org', 'stock_products.attribute_first_id', '=', 'attr1_org.id')
            ->leftJoin('attributes as attr2_org', 'stock_products.attribute_second_id', '=', 'attr2_org.id')
            ->leftJoin('variants as var1', 'attr1_org.variant_id', '=', 'var1.id')
            ->leftJoin('variants as var2', 'attr2_org.variant_id', '=', 'var2.id')
            ->whereNotNull('last_order_items.id')
            ->select([
                'stock_products.id',
                'stock_products.product_id',
                DB::raw("CASE
                    WHEN products.type = 'variable' THEN stock_products.sku
                    ELSE products.sku
                END AS sku"),
                DB::raw(
                    "CASE
                        WHEN products.type = 'variable' THEN
                            CONCAT(products.name, ' ',
                                COALESCE(var1.title, ''), ' ',
                                COALESCE(attr1_org.title, ''), ' ',
                                COALESCE(var2.title, ''), ' ',
                                COALESCE(attr2_org.title, '')
                            )
                        ELSE products.name
                     END AS product_name"
                ),
                'last_order_items.stock_id',
                'stocks.name as stock_name',
                'last_order_items.sell_price as sell_price_main',
                'last_order_items.purchase_price as purchase_price_main',
                DB::raw("CASE
                    WHEN products.type = 'combo' THEN stock_products.sell_price_combo
                    ELSE stock_products.sell_price
                END AS sell_price"),
                'stock_products.purchase_price',
                'last_order_items.quantity',
                'products.type as product_type',
                'stock_products.attribute_first_id',
                'stock_products.attribute_second_id',
                'u.name as unit_name',
                DB::raw("CASE
                    WHEN products.type = 'variable' THEN
                        COALESCE(CONCAT('$urlPrefix', product_variant_images.image), '$imageDefault')
                    ELSE
                        COALESCE(CONCAT('$urlPrefix', products.image_cover), '$imageDefault')
                END AS image"),
                'products.status',
                DB::raw('(
                    CASE
                        WHEN products.type = "variable" THEN (
                            SELECT JSON_ARRAYAGG(JSON_OBJECT(
                                "id", sp2.id,
                                "product_id", sp2.product_id,
                                "attribute_first_id", sp2.attribute_first_id,
                                "attribute_second_id", sp2.attribute_second_id,

                                "sell_price_main",
                                    CASE
                                        WHEN sp2.attribute_first_id = stock_products.attribute_first_id
                                        AND sp2.attribute_second_id = stock_products.attribute_second_id
                                        AND sp2.stock_id = last_order_items.stock_id
                                        THEN last_order_items.sell_price
                                        ELSE NULL
                                    END,
                                "purchase_price_main",
                                    CASE
                                        WHEN sp2.attribute_first_id = stock_products.attribute_first_id
                                        AND sp2.attribute_second_id = stock_products.attribute_second_id
                                        AND sp2.stock_id = last_order_items.stock_id
                                        THEN last_order_items.purchase_price
                                        ELSE NULL
                                    END,
                                "quantity",
                                    CASE
                                        WHEN sp2.attribute_first_id = stock_products.attribute_first_id
                                        AND sp2.attribute_second_id = stock_products.attribute_second_id
                                        AND sp2.stock_id = last_order_items.stock_id
                                        THEN last_order_items.quantity
                                        ELSE NULL
                                    END,

                                "stock_name", s.name,
                                "attr1_title", attr1.title,
                                "attr2_title", attr2.title,
                                "stock_id", s.id,
                                "is_stock_default", s.is_default,
                                "sku", sp2.sku,
                                "image", COALESCE(CONCAT("' . $urlPrefix . '", pv.image), "' . $imageDefault . '"),

                                "sell_price", (CASE WHEN products.type = \'combo\' THEN sp2.sell_price_combo ELSE sp2.sell_price END),
                                "purchase_price", sp2.purchase_price
                            ))
                            FROM stock_products sp2
                            JOIN stocks s ON sp2.stock_id = s.id
                            LEFT JOIN attributes attr1 ON sp2.attribute_first_id = attr1.id
                            LEFT JOIN attributes attr2 ON sp2.attribute_second_id = attr2.id
                            LEFT JOIN (
                                SELECT t1.*
                                FROM product_variant_images t1
                                INNER JOIN (
                                    SELECT stock_product_id, MIN(id) AS min_id
                                    FROM product_variant_images
                                    GROUP BY stock_product_id
                                ) t2 ON t1.stock_product_id = t2.stock_product_id AND t1.id = t2.min_id
                            ) AS pv ON pv.stock_product_id = sp2.id
                            WHERE sp2.product_id = stock_products.product_id
                            AND sp2.product_type = "variable"
                        )
                        ELSE (
                            SELECT JSON_ARRAYAGG(JSON_OBJECT(
                                "id", sp2.id,
                                "product_id", sp2.product_id,
                                "attribute_first_id", sp2.attribute_first_id,
                                "attribute_second_id", sp2.attribute_second_id,

                                "sell_price_main",
                                    CASE
                                        WHEN sp2.stock_id = last_order_items.stock_id THEN last_order_items.sell_price
                                        ELSE NULL
                                    END,
                                "purchase_price_main",
                                    CASE
                                        WHEN sp2.stock_id = last_order_items.stock_id THEN last_order_items.purchase_price
                                        ELSE NULL
                                    END,
                                "quantity",
                                    CASE
                                        WHEN sp2.stock_id = last_order_items.stock_id THEN last_order_items.quantity
                                        ELSE NULL
                                    END,

                                "stock_name", s.name,
                                "attr1_title", NULL,
                                "attr2_title", NULL,
                                "stock_id", s.id,
                                "is_stock_default", s.is_default,
                                "sku", sp2.sku,
                                "image", COALESCE(CONCAT("' . $urlPrefix . '", p.image_cover), "' . $imageDefault . '"),

                                "sell_price", (CASE WHEN products.type = \'combo\' THEN sp2.sell_price_combo ELSE sp2.sell_price END),
                                "purchase_price", sp2.purchase_price
                            ))
                            FROM stock_products sp2
                            JOIN stocks s ON sp2.stock_id = s.id
                            JOIN products p ON sp2.product_id = p.id
                            WHERE sp2.product_id = stock_products.product_id
                            AND sp2.parent_id IS NULL
                            AND sp2.product_type != "variable"
                        )
                    END
                ) as stocks')
            ]);
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('products.name', 'like', "%$search%")
                    ->orWhere('stock_products.sku', 'like', "%$search%");
            });
        }
        if (!empty($exceptIds)) {
            $query->whereNotIn('stock_products.id', $exceptIds);
        }
        $query->orderBy('stock_products.id', 'desc');
        return $query->paginate($request['limit'] ?? 10);
    }

    public function getInitOrder(Request $request)
    {
        if (isset($request['get_all']) && $request['get_all'] == 1) {
            return $this->getAllProducts($request);
        } else {
            if (isset($request['transaction_type']) && $request['transaction_type']) {
                $typeOrder = $request['transaction_type'];
                switch ($typeOrder) {
                    case 'sell':
                        break;
                    case 'price_quote':
                        return $this->getProductsByTypeOrderPriceQuote($request);
                        break;
                    case 'sell_return':
                        # code...
                        break;
                    default:
                        # code...
                        break;
                }
            }
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
            // 1. Upload ảnh bìa
            $coverPath = null;
            if ($request->hasFile('cover_image')) {
                $coverPath = $this->uploadFile($request->file('cover_image'), 'products/cover');
            }

            $variantInputMode = $request->input('variant_input_mode', 'create');

            // 2. Tạo sản phẩm chính
            $product = Product::create([
                'name'               => $request->input('name'),
                'sku'                => $request->input('sku'),
                'barcode'            => $request->input('barcode'),
                'type'               => $request->input('type'),
                'has_variant'        => $request->boolean('has_variant'),
                'has_serial'         => $request->boolean('has_serial'),
                'warranty'           => $request->input('warranty'),
                'unit_id'            => $request->input('unit_id'),
                'brand_id'           => $request->input('brand_id'),
                'category_id'        => $request->input('category_id'),
                'supplier_id'        => $request->input('supplier_id'),
                'image_cover'        => $coverPath,
                'variant_input_mode' => $variantInputMode,
                'status'             => 'pending',
            ]);

            // 3. Lưu ảnh gallery
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $file) {
                    $path = $this->uploadFile($file, 'products/gallery');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image'      => $path,
                    ]);
                }
            }

            // 4. Lưu stock_data (gốc)
            $stockData = json_decode($request->input('stock_data'), true);
            $totalSellPriceCombo = $this->getTotalSellPriceCombo($request['combo'] ?? []) ?? 0;
            foreach ($stockData as $item) {
                StockProduct::create([
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
                    'sell_price_combo'     => $request['type'] === 'combo' ? $totalSellPriceCombo + ($item['sell_price'] ?? 0) : 0
                ]);
            }

            // 5. Xử lý biến thể
            if ($request->has('variants')) {
                $variants = $request->input('variants');
                $customAttributes = json_decode($request->input('custom_attributes'), true) ?? [];

                $attributeMap = [];
                $valueMap = [];

                $variantCreate = $variantInputMode === 'create';
                foreach ($variants as $i => $variant) {
                    $attr1 = $variant['attributes'][0] ?? null;
                    $attr2 = $variant['attributes'][1] ?? null;

                    $resolveValueId = function ($attr) use (
                        &$attributeMap,
                        &$valueMap,
                        $variantInputMode,
                        $product,
                        $customAttributes,
                        $variantCreate
                    ) {
                        if (!$attr) {
                            return null;
                        }

                        $attrId = $attr['attribute_id'];
                        $valId = $attr['value_id'];

                        if ($variantCreate) {
                            // Tạo attribute nếu là chuỗi tạm
                            if (Str::startsWith($attrId, 'attr#') && !isset($attributeMap[$attrId])) {
                                $attrTitle = collect($customAttributes)
                                    ->firstWhere('id', $attrId)['title'] ?? 'Thuộc tính';
                                $createdAttr = Variant::create([
                                    'title'      => $attrTitle,
                                    'product_id' => $product->id,
                                ]);
                                $attributeMap[$attrId] = $createdAttr->id;
                            }

                            $realAttrId = $attributeMap[$attrId] ?? $attrId;

                            // Tạo value nếu là chuỗi tạm
                            if (Str::startsWith($valId, 'val#') && !isset($valueMap[$valId])) {
                                $valList = collect($customAttributes)
                                    ->firstWhere('id', $attrId)['values'] ?? [];

                                $valTitle = collect($valList)
                                    ->firstWhere('id', $valId)['title'] ?? 'Giá trị';

                                $createdVal = Attribute::create([
                                    'attribute_id' => $realAttrId,
                                    'title'        => $valTitle,
                                    'variant_id'   => $realAttrId,
                                ]);
                                $valueMap[$valId] = $createdVal->id;
                            }

                            return $valueMap[$valId] ?? null;
                        }

                        // from_category → dùng luôn ID thật
                        return $valId;
                    };

                    $firstId = $variantCreate ? $resolveValueId($attr1) : ($variant['attributes'][0]['value_id'] ?? null);
                    $secondId = $variantCreate ? $resolveValueId($attr2) : ($variant['attributes'][1]['value_id'] ?? null);

                    $variantStock = StockProduct::create([
                        'stock_id'            => $variant['stock_id'],
                        'product_id'          => $product->id,
                        'attribute_first_id'  => $firstId,
                        'attribute_second_id' => $secondId,
                        'quantity'            => $variant['quantity'],
                        'sell_price'          => $variant['sell_price'],
                        'purchase_price'      => $variant['purchase_price'],
                        'sku'                 => $variant['sku'] ?: $this->generateUniqueSku($product->sku ?? 'SP'),
                        'barcode'             => $variant['barcode'],
                        'is_sale'             => $variant['is_sale'],
                        'is_product_variant'  => 1,
                        'product_type'        => 'variable',
                    ]);

                    // Ảnh biến thể
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

            // 6. Xử lý combo (nếu có)
            if ($request->input('type') === 'combo') {
                $combos = json_decode($request->input('combo'), true) ?? [];
                $comboData = $this->getPrepareCombo($product->id, $combos);
                if (!$this->syncProductsCombo($product->id, $comboData)) {
                    DB::rollBack();
                    return $this->responseError(__('common.product.save_combo_failed'), 500);
                }
            }

            DB::commit();
            return $this->responseSuccess(true, __('common.product.create_success'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return $this->responseError(__('common.product.create_failed'), 500);
        }
    }

    public function getTotalSellPriceCombo($arrays = [])
    {
        if (is_string($arrays)) {
            $arrays = json_decode($arrays, true);
        }

        return array_reduce($arrays, function ($carry, $item) {
            return $carry + ($item['sell_price_combo'] ?? 0);
        }, 0);
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
                        'custom_attributes.values'
                    ]));

                    return $this->responseSuccess([
                        'product'        => $product,
                        'stock_products' => ProductAttributesResource::collection($product->attributes ?? []),
                    ]);

                case 'combo':
                    $product->load(array_merge($baseRelations, [
                        'combo.parent',
                        'combo.parent.attributeFirst:id,title,variant_id',
                        'combo.parent.attributeSecond:id,title,variant_id',
                        'combo.parent.attributeFirst.variant:id,title',
                        'combo.parent.attributeSecond.variant:id,title',
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
            $product = Product::findOrFail($id);
            if (!$product) {
                throw new Exception(__('common.product.not_found'));
            }
            $oldModeVariant = $product['variant_input_mode'];
            // 1. Cập nhật dữ liệu cơ bản
            $coverPath = $request->hasFile('cover_image')
                ? $this->uploadFile($request->file('cover_image'), 'products/cover')
                : null;
            $variantInputMode = $request->input('variant_input_mode', 'create');
            $data = [
                'name'               => $request->input('name'),
                'sku'                => $request->input('sku', ''),
                'barcode'            => $request->input('barcode', ''),
                'has_serial'         => $request->input('has_serial', 0),
                'warranty'           => $request->input('warranty'),
                'unit_id'            => $request->input('unit_id'),
                'brand_id'           => $request->input('brand_id'),
                'category_id'        => $request->input('category_id'),
                'supplier_id'        => $request->input('supplier_id'),
                'description'        => $request->input('description', ''),
                'has_variant'        => $request->input('has_variant', 0),
                'type'               => $request->input('type', 'single'),
                'status'             => 'pending',
                'variant_input_mode' => ($product->type === 'variable' && $request['type'] === 'single') ? null : $variantInputMode
            ];

            if ($request->boolean('remove_cover_image')) {
                $data['image_cover'] = null;
            } elseif ($coverPath) {
                $data['image_cover'] = $coverPath;
            }

            if ($product->type === 'variable' && $data['type'] === 'single') {
                $data['has_variant'] = 0;
                StockProduct::where('product_id', $product->id)
                    ->where('product_type', 'variable')
                    ->delete();
            }

            $product->update($data);

            // 2. Gallery ảnh
            $deletedIds = $request->input('deleted_gallery_ids', []);

            if (is_string($deletedIds)) {
                $decoded = json_decode($deletedIds, true);
                $deletedIds = is_array($decoded) ? $decoded : [];
            }

            $deletedIds = array_filter($deletedIds, 'is_numeric');
            if (count($deletedIds) > 0) {
                ProductImage::whereIn('id', $deletedIds)->delete();
            }

            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $file) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image'      => $this->uploadFile($file, 'products/gallery'),
                    ]);
                }
            }

            // 3. Stock gốc
            $totalSellPriceCombo = $this->getTotalSellPriceCombo($request['combo'] ?? []) ?? 0;
            $stocks = json_decode($request->input('stock_data'), true);
            if (empty($stocks)) {
                throw new Exception(__('common.product.missing_stock'));
            }
            $stocksInsert = [];
            $stocksUpdate = [];
            foreach ($stocks as $stock) {
                $data = [
                    'id'                   => $stock['id'] ?? null,
                    'product_id'           => $product->id,
                    'stock_id'             => $stock['stock_id'],
                    'quantity'             => $stock['quantity'] ?? 0,
                    'purchase_price'       => $stock['purchase_price'] ?? 0,
                    'sell_price'           => $stock['sell_price'] ?? 0,
                    'max_discount_percent' => $stock['max_discount_percent'] ?? 0,
                    'max_increase_percent' => $stock['max_increase_percent'] ?? 0,
                    'auto_calc'            => $stock['auto_calc'] ?? 0,
                    'product_type'         => 'root_stock',
                    'sell_price_combo'     => $request['type'] === 'combo' ? $totalSellPriceCombo + ($stock['sell_price'] ?? 0) : 0
                ];
                if ($data['id']) {
                    $stocksUpdate[] = $data;
                } else {
                    unset($data['id']);
                    $stocksInsert[] = $data;
                }
            }
            if (!empty($stocksUpdate)) {
                batch()->update(new StockProduct(), $stocksUpdate, 'id');
            }
            if (!empty($stocksInsert)) {
                StockProduct::insert($stocksInsert);
            }
            $removeIds = (array) $request->input('remove_stock_ids', []);
            if (count($removeIds) > 0) {
                StockProduct::where('product_id', $product->id)
                    ->where('product_type', 'root_stock')
                    ->whereIn('stock_id', $removeIds)
                    ->delete();
            }

            // 4. Biến thể
            if ($request->input('type') === 'variable') {
                $variants = $request->input('variants', []);
                if (empty($variants)) {
                    throw new Exception(__('common.product.missing_variant'));
                }

                $customAttributes = json_decode($request->input('custom_attributes'), true) ?? [];

                $attributeMap = [];
                $valueMap = [];

                $keptIds = [];
                $imageTracked = [];
                $variantKeptIds = [];
                $attributeKeptIds = [];
                $variantCreate = $variantInputMode === 'create';

                if ($oldModeVariant === 'from_category' && $request['variant_input_mode'] === 'create') {
                    StockProduct::where('product_id', $product['id'])
                        ->where('product_type', 'variable')
                        ->delete();
                }
                foreach ($variants as $i => $variant) {
                    $attr1 = $variant['attributes'][0] ?? null;
                    $attr2 = $variant['attributes'][1] ?? null;

                    $resolve = function ($attr) use (
                        $variantInputMode,
                        $product,
                        &$attributeMap,
                        &$valueMap,
                        $customAttributes,
                        &$variantKeptIds,
                        &$attributeKeptIds,
                        $variantCreate
                    ) {
                        if (!$attr) {
                            return null;
                        }

                        $attrId = $attr['attribute_id'];
                        $valId = $attr['value_id'];
                        // Nếu đang dùng create mode (tạo thuộc tính/giá trị mới)
                        if ($variantCreate) {
                            // === TẠO HOẶC CẬP NHẬT ATTRIBUTE ===
                            if (Str::startsWith($attrId, 'attr#')) {
                                if (!isset($attributeMap[$attrId])) {
                                    $title = collect($customAttributes)->firstWhere('id', $attrId)['title'] ?? 'Thuộc tính';
                                    $attributeMap[$attrId] = Variant::create([
                                        'title'      => $title,
                                        'product_id' => $product->id,
                                    ])->id;
                                    if (!in_array($attributeMap[$attrId], $variantKeptIds)) {
                                        $variantKeptIds[] = $attributeMap[$attrId];
                                    }
                                }
                            } elseif (is_numeric($attrId)) {
                                // Nếu là int (edit) → update title nếu có
                                $item = collect($customAttributes)->firstWhere('id', (int) $attrId);
                                if ($item && isset($item['title'])) {
                                    Variant::where('id', $attrId)->update(['title' => $item['title']]);
                                    if (!in_array($attrId, $variantKeptIds)) {
                                        $variantKeptIds[] = $attrId;
                                    }
                                }
                            }

                            $realAttrId = $attributeMap[$attrId] ?? $attrId;

                            // === TẠO HOẶC CẬP NHẬT VALUE ===
                            if (Str::startsWith($valId, 'val#')) {
                                if (!isset($valueMap[$valId])) {
                                    $valList = collect($customAttributes)->firstWhere('id', $attrId)['values'] ?? [];
                                    $title = collect($valList)->firstWhere('id', $valId)['title'] ?? 'Giá trị';
                                    $valueMap[$valId] = Attribute::create([
                                        'title'      => $title,
                                        'variant_id' => $realAttrId,
                                    ])->id;
                                    if (!in_array($valueMap[$valId], $attributeKeptIds)) {
                                        $attributeKeptIds[] = $valueMap[$valId];
                                    }
                                }
                            } elseif (is_numeric($valId)) {
                                $values = collect($customAttributes)->firstWhere('id', $attrId)['values'] ?? [];
                                $item = collect($values)->firstWhere('id', (int) $valId);
                                if ($item && isset($item['title'])) {
                                    Attribute::where('id', $valId)->update([
                                        'title' => $item['title']
                                    ]);
                                    if (!in_array($valId, $attributeKeptIds)) {
                                        $attributeKeptIds[] = $valId;
                                    }
                                }
                            }

                            return $valueMap[$valId] ?? $valId;
                        }

                        return $valId;
                    };

                    $firstId = $variantCreate ? $resolve($attr1) : ($variant['attributes'][0]['value_id'] ?? null);
                    $secondId = $variantCreate ? $resolve($attr2) : ($variant['attributes'][1]['value_id'] ?? null);

                    $sku = $variant['sku'] ?: $this->generateUniqueSku($request->input('sku') ?? 'SP', $variant['id'] ?? null);

                    $data = [
                        'id'                   => $variant['id'] ?? null,
                        'product_id'           => $product->id,
                        'stock_id'             => $variant['stock_id'],
                        'quantity'             => $variant['quantity'] ?? 0,
                        'sell_price'           => $variant['sell_price'] ?? 0,
                        'purchase_price'       => $variant['purchase_price'] ?? 0,
                        'sku'                  => $sku,
                        'barcode'              => $variant['barcode'] ?? '',
                        'is_sale'              => $variant['is_sale'] ?? 1,
                        'max_discount_percent' => $variant['max_discount_percent'] ?? 0,
                        'max_increase_percent' => $variant['max_increase_percent'] ?? 0,
                        'auto_calc'            => $variant['auto_calc'] ?? 1,
                        'is_using'             => $variant['is_using'] ?? 1,
                        'attribute_first_id'   => $firstId,
                        'attribute_second_id'  => $secondId,
                        'product_type'         => 'variable',
                        'is_product_variant'   => 1,
                    ];

                    $variantModel = $data['id']
                        ? tap(StockProduct::findOrFail($data['id']))->update($data)
                        : StockProduct::create($data);

                    $keptIds[] = $variantModel->id;

                    // Ảnh biến thể
                    if ($request->hasFile("variants.$i.image")) {
                        $imgPath = $this->uploadFile($request->file("variants.$i.image"), 'products/variants');
                        $img = ProductVariantImage::create([
                            'stock_product_id' => $variantModel->id,
                            'image'            => $imgPath,
                        ]);
                        if ($img) {
                            $imageTracked[] = $img->id;
                        }
                    }
                }

                if (!empty($variantKeptIds)) {
                    Variant::where('product_id', $product['id'])
                        ->whereNotIn('id', $variantKeptIds)
                        ->delete();
                }
                if (!empty($attributeKeptIds)) {
                    Attribute::whereHas('variant', function ($q) use ($product) {
                        $q->where('product_id', $product['id']);
                    })
                        ->whereNotIn('id', $attributeKeptIds)
                        ->delete();
                }
                if ($oldModeVariant === 'create' && $request['variant_input_mode'] === 'from_category') {
                    Variant::where('product_id', $product['id'])
                        ->delete();
                }
                // Xoá ảnh thừa
                $removedImageIds = $request->input('removed_variant_image_ids', []);

                if (is_string($removedImageIds)) {
                    $removedImageIds = json_decode($removedImageIds, true) ?? [];
                }
                $removedImageIds = array_filter($removedImageIds, 'is_numeric');

                ProductVariantImage::whereNotIn('id', $imageTracked)
                    ->whereIn('stock_product_id', $removedImageIds)
                    ->delete();

                // Xoá biến thể không còn dùng
                StockProduct::where('product_id', $product->id)
                    ->where('product_type', 'variable')
                    ->whereNotIn('id', $keptIds)
                    ->delete();
            }

            // 5. Combo
            if ($request->input('type') === 'combo') {
                $combos = json_decode($request->input('combo'), true) ?? [];
                $syncData = $this->getPrepareCombo($product->id, $combos);
                if (!$this->syncProductsCombo($product->id, $syncData)) {
                    DB::rollBack();
                    return $this->responseError(__('common.product.save_combo_failed'));
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
