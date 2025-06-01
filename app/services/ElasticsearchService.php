<?php

namespace App\Services;

use Elastic\Elasticsearch\ClientBuilder;

class ElasticsearchService
{
  protected $client;

  public function __construct()
  {
    $this->client = ClientBuilder::create()
      ->setHosts(config('elasticsearch.hosts'))
      ->build();
  }

  public function client()
  {
    return $this->client;
  }

  public function search(string $index, array $body)
  {
    return $this->client->search([
      'index' => $index,
      'body' => $body
    ]);
  }

  public function index(string $index, string|int $id, array $data)
  {
    return $this->client->index([
      'index' => $index,
      'id' => $id,
      'body' => $data
    ]);
  }

  public function delete(string $index, string|int $id)
  {
    return $this->client->delete([
      'index' => $index,
      'id' => $id
    ]);
  }
}
