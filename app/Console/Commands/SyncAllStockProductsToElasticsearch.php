<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Jobs\SyncStockProductToElasticsearchJob;

class SyncAllStockProductsToElasticsearch extends Command
{
    protected $signature = 'es:sync-stock-products';
    protected $description = 'Đồng bộ toàn bộ stock_products lên Elasticsearch';

    public function handle()
    {
        $ids = DB::table('stock_products')->pluck('id');

        $this->info('🔁 Đang gửi ' . count($ids) . ' job lên queue...');

        foreach ($ids as $id) {
            dispatch(new SyncStockProductToElasticsearchJob($id));
        }

        $this->info('✅ Đã gửi toàn bộ job đồng bộ lên queue.');
    }
}
