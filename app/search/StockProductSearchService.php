<?php

namespace App\Search;

use App\Services\ElasticsearchService;

class StockProductSearchService
{
  protected $es;

  public function __construct(ElasticsearchService $es)
  {
    $this->es = $es;
  }

  public function search(string $keyword, int $page = 1, int $limit = 20)
  {
    $from = ($page - 1) * $limit;

    return $this->es->search('stock_products', [
      'from' => $from,
      'size' => $limit,
      'query' => [
        'multi_match' => [
          'query' => $keyword,
          'fields' => ['product_name^3', 'sku'],
          'fuzziness' => 'AUTO',
        ]
      ]
    ]);
  }
}
