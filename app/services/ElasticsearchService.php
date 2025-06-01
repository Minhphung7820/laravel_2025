<?php

namespace App\Services;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;

class ElasticsearchService
{
  /**
   * The Elasticsearch client instance.
   *
   * @var Client
   */
  protected $client;

  /**
   * Create a new ElasticsearchService instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->client = ClientBuilder::create()
      ->setHosts(config('elasticsearch.hosts'))
      ->build();
  }

  /**
   * Get the Elasticsearch client instance.
   *
   * @return Client
   */
  public function client()
  {
    return $this->client;
  }

  /**
   * Perform a search query on the specified index.
   *
   * @param string $index The name of the index
   * @param array $body The search query body
   * @return array
   */
  public function search(string $index, array $body)
  {
    return $this->client->search([
      'index' => $index,
      'body' => $body
    ]);
  }

  /**
   * Index or update a document in Elasticsearch.
   *
   * @param string $index The name of the index
   * @param string|int $id The ID of the document
   * @param array $data The data to be indexed
   * @return array
   */
  public function index(string $index, string|int $id, array $data)
  {
    return $this->client->index([
      'index' => $index,
      'id' => $id,
      'body' => $data
    ]);
  }

  /**
   * Delete a document from Elasticsearch.
   *
   * @param string $index The name of the index
   * @param string|int $id The ID of the document
   * @return array
   */
  public function delete(string $index, string|int $id)
  {
    return $this->client->delete([
      'index' => $index,
      'id' => $id
    ]);
  }
}
