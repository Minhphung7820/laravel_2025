<?php

namespace App\Console\Commands;

use App\Jobs\SyncStockProductToElasticsearchJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncAllStockProductsToElasticsearch extends Command
{
    protected $signature = 'es:sync-stock-products';
    protected $description = 'Đồng bộ toàn bộ stock_products lên Elasticsearch';

    public function handle()
    {
        // $this->info('🔁 Bắt đầu gửi job sync lên queue...');

        // $count = 0;
        // foreach (DB::table('stock_products')->select('id')->cursor() as $row) {
        //     dispatch(new SyncStockProductToElasticsearchJob($row->id));
        //     $count++;
        // }

        // $this->info("✅ Đã gửi $count job đồng bộ lên queue.");
    }
}
