<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ElasticsearchService;

class EsCreateStockProductIndex extends Command
{
    protected $signature = 'es:create-stock-products-index';
    protected $description = 'Tạo index stock_products trong Elasticsearch';

    public function handle(ElasticsearchService $es)
    {
        // $client = $es->client();

        // // Nếu đã tồn tại thì xóa
        // if ($client->indices()->exists(['index' => 'stock_products'])->asBool()) {
        //     $this->warn('🔁 Index đã tồn tại. Đang xóa để tạo lại...');
        //     $client->indices()->delete(['index' => 'stock_products']);
        // }

        // $client->indices()->create([
        //     'index' => 'stock_products',
        //     'body' => [
        //         'mappings' => [
        //             'properties' => [
        //                 'product_id'    => ['type' => 'integer'],
        //                 'product_name'  => ['type' => 'text'],
        //                 'sku'           => ['type' => 'text'],
        //                 'status'        => ['type' => 'keyword'],
        //                 'product_type'  => ['type' => 'keyword'],
        //                 'image'         => ['type' => 'keyword'],
        //             ]
        //         ]
        //     ]
        // ]);

        // $this->info('✅ Tạo index `stock_products` thành công.');
    }
}
