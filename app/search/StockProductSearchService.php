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

  public function search(string $keyword, int $limit = 20, ?string $afterId = null)
  {
    $body = [
      'size' => $limit,
      'sort' => [
        ['_id' => 'asc']
      ],
      'query' => [
        'multi_match' => [
          'query' => $keyword,
          'fields' => ['product_name^3', 'sku'],
          'fuzziness' => 'AUTO',
        ]
      ]
    ];

    if ($afterId) {
      $body['search_after'] = [$afterId];
    }

    return $this->es->search('stock_products', $body);
  }
}
