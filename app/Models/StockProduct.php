<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StockProduct extends Model
{
    protected $fillable = [
        'stock_id',
        'product_id',
        'attribute_first_id',
        'attribute_second_id',
        'quantity',
        'sell_price',
        'purchase_price',
        'product_type',
        'barcode',
        'sku',
        'unit_id',
        'is_sale',
        'is_product_variant',
        'max_discount_percent',
        'max_increase_percent',
        'auto_calc',
        'is_using',
        'quantity_combo',
        'sell_price_combo',
        'parent_id'
    ];

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function parent()
    {
        $imagePrefix = url('/');
        $imageDefault = env('IMAGE_DEFAULT');

        return $this->belongsTo(StockProduct::class, 'parent_id')
            ->select('stock_products.*')
            ->addSelect([
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
                    "image", COALESCE(CONCAT("' . $imagePrefix . '", pvi.image), "' . $imageDefault . '")
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
                AND (sp2.attribute_first_id <=> stock_products.attribute_first_id)
                AND (sp2.attribute_second_id <=> stock_products.attribute_second_id)
                AND sp2.product_type = "variable"
            ) AS related_variants')
            ]);
    }

    public function attributeFirst()
    {
        return $this->belongsTo(Attribute::class, 'attribute_first_id');
    }

    public function attributeSecond()
    {
        return $this->belongsTo(Attribute::class, 'attribute_second_id');
    }

    public function variantImages()
    {
        return $this->hasMany(ProductVariantImage::class);
    }
}
