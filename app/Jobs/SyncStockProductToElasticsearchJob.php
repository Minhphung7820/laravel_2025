<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use App\Services\ElasticsearchService;

class SyncStockProductToElasticsearchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $stockProductId;

    public function __construct($stockProductId)
    {
        $this->stockProductId = $stockProductId;
    }

    public function handle(ElasticsearchService $es)
    {
        $sp = DB::table('stock_products as st')
            ->join('products as p', 'st.product_id', '=', 'p.id')
            ->leftJoin('stocks as s', 'st.stock_id', '=', 's.id')
            ->select([
                'st.id',
                'p.id as product_id',
                'p.name as product_name',
                'st.sku',
                'p.status',
                'p.type as product_type',
                'st.created_at',
                DB::raw("CONCAT('" . url('/') . "',
        CASE WHEN p.type = 'variable'
        THEN (SELECT image FROM product_variant_images WHERE stock_product_id = st.id LIMIT 1)
        ELSE p.image_cover END) AS image")
            ])
            ->where('st.id', $this->stockProductId)
            ->first();

        if ($sp) {
            $es->index('stock_products', $sp->id, (array) $sp);
        }
    }
}
