<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CrmSeeder extends Seeder
{
    public function run(): void
    {
        // Products
        $products = [];
        $types = ['single', 'combo', 'variable'];
        for ($i = 1; $i <= 10; $i++) {
            $products[] = [
                'id'         => $i,
                'name'       => 'Product ' . $i,
                'type'       => $types[array_rand($types)],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('products')->insert($products);

        // Variants
        DB::table('variants')->insert([
            ['id' => 1, 'title' => 'Color', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'title' => 'Size', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Attributes (Color and Size)
        $attributes = [];
        $colors = ['Red', 'Blue', 'Green', 'Black', 'White', 'Yellow'];
        $sizes = ['S', 'M', 'L', 'XL', 'XXL', 'XXXL'];
        $id = 1;
        foreach ($colors as $color) {
            $attributes[] = ['id' => $id++, 'title' => $color, 'variant_id' => 1, 'created_at' => now(), 'updated_at' => now()];
        }
        foreach ($sizes as $size) {
            $attributes[] = ['id' => $id++, 'title' => $size, 'variant_id' => 2, 'created_at' => now(), 'updated_at' => now()];
        }
        DB::table('attributes')->insert($attributes);

        // Stocks
        $stocks = [
            ['id' => 1, 'name' => 'Warehouse Saigon', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Warehouse Hanoi', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Warehouse Danang', 'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('stocks')->insert($stocks);

        // Stock Products
        $stockProducts = [];
        $spId = 1;
        foreach ($products as $product) {
            foreach ($stocks as $stock) {
                if ($product['type'] !== 'variable') {
                    $stockProducts[] = [
                        'id'                  => $spId++,
                        'stock_id'            => $stock['id'],
                        'product_id'          => $product['id'],
                        'quantity'            => rand(10, 100),
                        'sell_price'          => rand(100000, 500000),
                        'purchase_price'      => rand(80000, 400000),
                        'product_type'        => $product['type'],
                        'attribute_first_id'  => null,
                        'attribute_second_id' => null,
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ];
                } else {
                    foreach (range(1, 6) as $colorId) {
                        foreach (range(7, 12) as $sizeId) {
                            $stockProducts[] = [
                                'id'                  => $spId++,
                                'stock_id'            => $stock['id'],
                                'product_id'          => $product['id'],
                                'quantity'            => rand(1, 20),
                                'sell_price'          => rand(150000, 600000),
                                'purchase_price'      => rand(100000, 500000),
                                'product_type'        => $product['type'],
                                'attribute_first_id'  => $colorId,
                                'attribute_second_id' => $sizeId,
                                'created_at'          => now(),
                                'updated_at'          => now(),
                            ];
                        }
                    }
                }
            }
        }
        DB::table('stock_products')->insert($stockProducts);

        // Orders + Order Items for 100 random users
        $transactionTypes = ['provide', 'material', 'other'];
        $statuses = ['create', 'completed'];
        $orders = [];
        $items = [];
        $orderId = 1;
        $itemId = 1;

        for ($u = 0; $u < 100; $u++) {
            $userId = rand(1, 1000);
            $orderCount = rand(1, 3);
            for ($o = 0; $o < $orderCount; $o++) {
                $transactionDate = Carbon::now()->subDays(rand(0, 60));
                $orders[] = [
                    'id'               => $orderId,
                    'code'             => 'ORD-' . Str::upper(Str::random(8)),
                    'transaction_type' => $transactionTypes[array_rand($transactionTypes)],
                    'transaction_date' => $transactionDate,
                    'user_id'          => $userId,
                    'status'           => $statuses[array_rand($statuses)],
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];

                $itemCount = rand(1, 4);
                for ($i = 0; $i < $itemCount; $i++) {
                    $sp = $stockProducts[array_rand($stockProducts)];
                    $items[] = [
                        'id'                  => $itemId++,
                        'order_id'            => $orderId,
                        'product_id'          => $sp['product_id'],
                        'stock_id'            => $sp['stock_id'],
                        'stock_product_id'    => $sp['id'],
                        'attribute_first_id'  => $sp['attribute_first_id'],
                        'attribute_second_id' => $sp['attribute_second_id'],
                        'product_type'        => $sp['product_type'],
                        'quantity'            => rand(1, 5),
                        'sell_price'          => $sp['sell_price'],
                        'purchase_price'      => $sp['purchase_price'],
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ];
                }

                $orderId++;
            }
        }

        DB::table('orders')->insert($orders);
        DB::table('order_items')->insert($items);
    }
}
